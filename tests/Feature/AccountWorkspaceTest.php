<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ProfileChangeRequest;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email = 'person@example.test', string $role = 'customer'): User
    {
        return User::create(['email' => $email, 'first_name' => 'Tom', 'last_name' => 'Sender', 'password' => 'Initial-password-123!', 'role' => $role, 'status' => 'active']);
    }

    private function customer(User $user): Customer
    {
        $customer = Customer::create(['name' => $user->name, 'email' => $user->email, 'tier' => 'standard']);
        $user->update(['customer_id' => $customer->id, 'approved_at' => now()]);

        return $customer;
    }

    private function shipment(Customer $customer, string $tracking = 'SHF-TEST-ACCOUNT'): Shipment
    {
        return Shipment::create(['customer_id' => $customer->id, 'tracking_number' => $tracking, 'status' => 'pending', 'service_type' => 'standard', 'origin_city' => 'Lagos', 'origin_country' => 'Nigeria', 'destination_city' => 'Abuja', 'destination_country' => 'Nigeria']);
    }

    public function test_customer_and_admin_sign_in_are_separate(): void
    {
        $admin = $this->user('staff@example.test', 'super_admin');
        $this->postJson('/login', ['email' => $admin->email, 'password' => 'Initial-password-123!'])->assertUnprocessable();
        $this->assertGuest();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'Initial-password-123!'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_customer_cannot_use_admin_sign_in_or_operations(): void
    {
        $user = $this->user();
        $this->postJson('/admin/login', ['email' => $user->email, 'password' => 'Initial-password-123!'])->assertUnprocessable();
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'Initial-password-123!'])->assertRedirect('/account');
        $this->get('/dashboard')->assertForbidden();
    }

    public function test_public_sign_in_hides_admin_entry_and_staff_sign_in_has_no_registration(): void
    {
        $this->get('/login')->assertOk()->assertDontSee('admin/login', false)->assertSee('Show password', false);
        $this->get('/admin/login')->assertOk()->assertSee('admin/login', false)->assertDontSee('Create account');
    }

    public function test_new_registration_requires_review_and_cannot_link_itself(): void
    {
        $this->post('/register', ['first_name' => 'New', 'email' => 'new@example.test', 'password' => 'Example-password-123!', 'customer_id' => 123, 'approved_at' => now()->toDateTimeString(), 'role' => 'super_admin'])->assertRedirect('/account');
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertNull($user->approved_at);
        $this->assertNull($user->customer_id);
        $this->assertSame('customer', $user->role);
        $this->get('/account')->assertOk()->assertSee('reviewing your account');
    }

    public function test_customer_only_sees_explicitly_linked_shipments(): void
    {
        $owner = $this->user();
        $other = $this->user('other@example.test');
        $ownShipment = $this->shipment($this->customer($owner));
        $private = $this->shipment($this->customer($other), 'SHF-OTHER-PRIVATE');
        $this->actingAs($owner)->get('/account')->assertOk()->assertSee($ownShipment->tracking_number)->assertDontSee($private->tracking_number);
        $this->get('/account/shipments/'.$private->id)->assertNotFound();
        $this->get('/account/shipments/'.$ownShipment->id)->assertOk()->assertSee('WHAT HAPPENS NEXT')->assertSee('Collection scan');
    }

    public function test_customer_cannot_read_other_invoices_or_notifications(): void
    {
        $owner = $this->user();
        $other = $this->user('other@example.test');
        $c = $this->customer($owner);
        $otherCustomer = $this->customer($other);
        $invoice = Invoice::create(['customer_id' => $otherCustomer->id, 'invoice_number' => 'INV-PRIVATE', 'amount' => 20, 'currency' => 'USD', 'status' => 'sent']);
        $notice = AppNotification::create(['user_id' => $other->id, 'type' => 'system', 'title' => 'Private', 'message' => 'Private note']);
        $this->actingAs($owner)->get('/account/invoices/'.$invoice->id)->assertNotFound();
        $this->patch('/account/notifications/'.$notice->id)->assertNotFound();
        $this->get('/account/invoices')->assertOk()->assertDontSee('INV-PRIVATE');
    }

    public function test_invoice_mismatched_to_another_customers_shipment_is_hidden(): void
    {
        $user = $this->user();
        $customer = $this->customer($user);
        $other = Customer::create(['name' => 'Other', 'tier' => 'standard']);
        $private = $this->shipment($other);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'shipment_id' => $private->id, 'invoice_number' => 'INV-MISMATCH', 'amount' => 20, 'currency' => 'USD', 'status' => 'sent']);
        $this->actingAs($user)->get('/account/invoices/'.$invoice->id)->assertNotFound();
    }

    public function test_approval_links_customer_and_sends_private_notification(): void
    {
        $user = $this->user();
        $admin = $this->user('admin@example.test', 'super_admin');
        $this->actingAs($admin)->post('/account-reviews/users/'.$user->id)->assertRedirect();
        $this->assertNotNull($user->fresh()->approved_at);
        $this->assertNotNull($user->fresh()->customer_id);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $user->id, 'title' => 'Your shipping account is ready']);
        $this->post('/account-reviews/users/'.$user->id)->assertRedirect();
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_existing_customer_link_cannot_be_claimed_twice(): void
    {
        $owner = $this->user();
        $c = $this->customer($owner);
        $new = $this->user('new@example.test');
        $admin = $this->user('admin@example.test', 'super_admin');
        $this->actingAs($admin)->postJson('/account-reviews/users/'.$new->id, ['customer_id' => $c->id])->assertUnprocessable();
        $this->assertNull($new->fresh()->customer_id);
    }

    public function test_profile_is_unchanged_until_another_admin_approves(): void
    {
        $user = $this->user();
        $admin = $this->user('admin@example.test', 'super_admin');
        $this->actingAs($user)->post('/settings/profile', ['first_name' => 'Thomas', 'last_name' => 'Sender', 'email' => 'updated@example.test'])->assertRedirect();
        $this->assertSame('Tom', $user->fresh()->first_name);
        $this->assertSame('person@example.test', $user->fresh()->email);
        $change = ProfileChangeRequest::firstOrFail();
        $this->actingAs($admin)->post('/account-reviews/profiles/'.$change->id, ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('Thomas', $user->fresh()->first_name);
        $this->assertSame('updated@example.test', $user->fresh()->email);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $user->id, 'title' => 'Profile request approved']);
    }

    public function test_profile_rejection_keeps_current_details_and_cannot_be_reviewed_twice(): void
    {
        $user = $this->user();
        $admin = $this->user('admin@example.test', 'super_admin');
        $this->actingAs($user)->post('/settings/profile', ['first_name' => 'Different', 'last_name' => 'Sender', 'email' => $user->email]);
        $change = ProfileChangeRequest::firstOrFail();
        $this->actingAs($admin)->post('/account-reviews/profiles/'.$change->id, ['decision' => 'rejected', 'review_note' => 'Confirm your details'])->assertRedirect();
        $this->assertSame('Tom', $user->fresh()->first_name);
        $this->postJson('/account-reviews/profiles/'.$change->id, ['decision' => 'approved'])->assertUnprocessable();
    }

    public function test_admin_cannot_approve_own_profile_request(): void
    {
        $admin = $this->user('admin@example.test', 'super_admin');
        $change = ProfileChangeRequest::create(['user_id' => $admin->id, 'changes' => ['first_name' => 'Other']]);
        $this->actingAs($admin)->postJson('/account-reviews/profiles/'.$change->id, ['decision' => 'approved'])->assertUnprocessable();
    }

    public function test_preferences_persist_and_settings_never_prefill_passwords(): void
    {
        $user = $this->user();
        $this->actingAs($user)->patch('/settings/preferences', ['theme' => 'dark', 'density' => 'compact', 'reduce_motion' => 1])->assertRedirect();
        $this->assertSame('dark', $user->fresh()->preferences['theme']);
        $this->get('/settings')->assertOk()->assertSee('Password & security', false)->assertDontSee('Initial-password-123!')->assertDontSee($user->password);
    }

    public function test_password_change_requires_current_password_and_invalidates_remember_token(): void
    {
        $user = $this->user();
        $this->actingAs($user)->patchJson('/settings/password', ['current_password' => 'wrong', 'password' => 'New-password-123!', 'password_confirmation' => 'New-password-123!'])->assertUnprocessable();
        $this->patch('/settings/password', ['current_password' => 'Initial-password-123!', 'password' => 'New-password-123!', 'password_confirmation' => 'New-password-123!'])->assertRedirect();
        $this->assertTrue(Hash::check('New-password-123!', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_suspended_customers_cannot_retain_workspace_access(): void
    {
        $user = $this->user();
        $user->update(['status' => 'suspended']);
        $this->actingAs($user)->get('/account')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_first_admin_command_has_no_default_and_never_resets_existing_admin(): void
    {
        $this->artisan('shipfast:provision-admin')->expectsQuestion('Administrator email', 'hosted@example.test')->expectsQuestion('Administrator first name', 'Hosted')->expectsQuestion('Administrator last name', 'Admin')->expectsQuestion('Choose a new administrator password (16–72 characters)', 'Hosted-password-123!')->expectsQuestion('Confirm the new password', 'Hosted-password-123!')->expectsOutput('HOSTED_ADMIN_CREATED')->assertSuccessful();
        $user = User::firstOrFail();
        $hash = $user->password;
        $this->artisan('shipfast:provision-admin')->assertFailed();
        $this->assertSame($hash,$user->fresh()->password);
    }
}

