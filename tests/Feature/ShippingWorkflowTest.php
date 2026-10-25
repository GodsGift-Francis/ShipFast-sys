<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\Shipment;
use App\Models\User;
use App\Services\RateEngine;
use App\Services\ShipmentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ShippingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['email' => 'operator@example.test', 'password' => 'test-password', 'role' => 'super_admin', 'status' => 'active']);
    }

    private function payload(array $changes = []): array
    {
        return array_replace(['service_type' => 'standard', 'origin_city' => 'Lagos', 'origin_country' => 'Nigeria', 'origin_address' => '1 Test Road', 'destination_city' => 'Abuja', 'destination_country' => 'Nigeria', 'destination_address' => '2 Test Road', 'sender_name' => 'Test Sender', 'recipient_name' => 'Test Recipient', 'weight' => 2, 'dimensions' => '30x20x10', 'contents_description' => 'Cotton shirts'], $changes);
    }

    private function shipment(array $changes = []): Shipment
    {
        return Shipment::create($this->payload($changes) + ['tracking_number' => ShipmentStatusService::trackingNumber(), 'status' => 'pending']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_volumetric_weight_price_breakdown_and_business_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-23 12:00:00'));
        $rate = app(RateEngine::class)->quote('standard', 8, '40x30x50', 'NG', 'Nigeria');
        $this->assertSame(12.0, $rate['billableWeight']);
        $this->assertSame(25.6, $rate['transport']);
        $this->assertSame(4.1, $rate['fuel']);
        $this->assertSame(29.7, $rate['cost']);
        $this->assertFalse($rate['international']);
        $this->assertSame('2026-10-28', $rate['estimatedDelivery']);
    }

    public function test_half_kg_rounding_and_minimum_adjustment(): void
    {
        $rate = app(RateEngine::class)->quote('standard', 0.1, null, 'Nigeria', 'NG');
        $this->assertSame(0.5, $rate['billableWeight']);
        $this->assertSame(8.0, $rate['cost']);
        $this->assertEquals(8, $rate['transport'] + $rate['fuel'] + $rate['minimumAdjustment']);
    }

    public function test_invalid_dimensions_are_rejected_instead_of_silently_underpricing(): void
    {
        $this->expectException(ValidationException::class);
        app(RateEngine::class)->quote('express', 2, '-30x20x10', 'Nigeria', 'Ghana');
    }

    public function test_zero_dimensions_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(RateEngine::class)->quote('standard', 2, '30x0x10', 'NG', 'NG');
    }

    public function test_retired_service_is_absent_and_country_aliases_match(): void
    {
        $this->postJson('/rates/estimate', ['weight' => 2, 'origin_country' => 'US', 'destination_country' => 'USA'])->assertOk()->assertJsonPath('rates.standard.international', false)->assertJsonMissingPath('rates.overnight');
        $this->postJson('/rates/estimate', ['weight' => 2, 'origin_country' => 'Nigeria', 'destination_country' => 'Ghana'])->assertOk()->assertJsonMissingPath('rates.overnight')->assertJsonCount(4, 'rates');
    }

    public function test_booking_creates_priced_shipment_and_initial_event_together(): void
    {
        $this->actingAs($this->admin())->post('/shipments', $this->payload())->assertRedirect();
        $shipment = Shipment::firstOrFail();
        $this->assertSame('pending', $shipment->status);
        $this->assertNotNull($shipment->rate_breakdown);
        $this->assertSame(1, $shipment->events()->count());
        $this->get('/shipments/'.$shipment->id)->assertOk()->assertSee($shipment->tracking_number);
        $this->get('/shipments/'.$shipment->id.'/waybill')->assertOk()->assertSee('not a carrier-issued shipping label');
    }

    public function test_cross_border_booking_requires_customs_information(): void
    {
        $this->actingAs($this->admin())->postJson('/shipments', $this->payload(['destination_country' => 'Ghana']))->assertUnprocessable()->assertJsonValidationErrors(['declared_value', 'country_of_manufacture']);
        $this->assertDatabaseCount('shipments', 0);
    }

    public function test_edit_booking_reprices_and_cannot_edit_after_collection(): void
    {
        $shipment = $this->shipment();
        $this->actingAs($this->admin())->patch('/shipments/'.$shipment->id, $this->payload(['weight' => 10]))->assertRedirect();
        $this->assertEquals(10, $shipment->fresh()->weight);
        $this->assertNotNull($shipment->fresh()->rate_breakdown);
        app(ShipmentStatusService::class)->apply($shipment, 'processing', notify: false);
        $this->patch('/shipments/'.$shipment->id, $this->payload())->assertForbidden();
    }

    public function test_forward_lifecycle_and_signature_confirmation(): void
    {
        $shipment = $this->shipment(['signature_required' => true]);
        $service = app(ShipmentStatusService::class);
        foreach (['processing', 'in_transit', 'out_for_delivery'] as $status) {
            $service->apply($shipment, $status, notify: false);
        }
        try {
            $service->apply($shipment, 'delivered', notify: false);
            $this->fail('Missing recipient must be rejected');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('delivered_to', $e->errors());
        }
        $this->assertSame('out_for_delivery', $shipment->fresh()->status);
        $this->assertSame(3, $shipment->events()->count());
        $service->apply($shipment, 'delivered', notify: false, deliveredTo: 'Test Recipient');
        $this->assertSame('Test Recipient', $shipment->fresh()->delivered_to);
        $this->assertNotNull($shipment->fresh()->actual_delivery);
        try {
            $service->apply($shipment, 'in_transit', notify: false);
            $this->fail('Cannot reopen delivered shipment');
        } catch (ValidationException $e) {
            $this->assertSame('delivered', $shipment->fresh()->status);
        }
    }

    public function test_skip_to_delivery_and_unknown_status_do_not_write_events(): void
    {
        $shipment = $this->shipment();
        foreach (['delivered', 'imaginary'] as $status) {
            try {
                app(ShipmentStatusService::class)->apply($shipment, $status, notify: false);
                $this->fail('Invalid transition accepted');
            } catch (ValidationException $e) {
                $this->assertSame(0, $shipment->events()->count());
            }
        }$this->assertSame('pending', $shipment->fresh()->status);
    }

    public function test_exception_needs_a_reason_then_can_resume(): void
    {
        $shipment = $this->shipment(['status' => 'in_transit']);
        $service = app(ShipmentStatusService::class);
        try {
            $service->apply($shipment, 'on_hold', notify: false);
            $this->fail('Exception reason required');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('description', $e->errors());
        }
        $service->apply($shipment, 'on_hold', description: 'Weather delay', notify: false);
        $service->apply($shipment, 'in_transit', notify: false);
        $this->assertSame('in_transit', $shipment->fresh()->status);
    }

    public function test_incomplete_quote_booking_cannot_be_collected(): void
    {
        $shipment = $this->shipment(['contents_description' => null]);
        $this->expectException(ValidationException::class);
        app(ShipmentStatusService::class)->apply($shipment, 'processing', notify: false);
    }

    public function test_quote_acceptance_is_idempotent_and_expired_quote_is_rejected(): void
    {
        $quote = Quote::create(['service_type' => 'standard', 'origin_city' => 'Lagos', 'origin_country' => 'Nigeria', 'destination_city' => 'Abuja', 'destination_country' => 'Nigeria', 'weight' => 2, 'estimated_cost' => 10, 'estimated_days' => 3, 'status' => 'pending', 'expires_at' => now()->addDay()]);
        $this->actingAs($this->admin())->post('/quotes/'.$quote->id.'/accept')->assertRedirect();
        $id = $quote->fresh()->shipment_id;
        $this->assertNotNull($id);
        $this->post('/quotes/'.$quote->id.'/accept')->assertRedirect('/shipments/'.$id);
        $this->assertDatabaseCount('shipments', 1);
        $expired = $quote->replicate(['shipment_id']);
        $expired->status = 'pending';
        $expired->expires_at = now()->subDay();
        $expired->save();
        $this->postJson('/quotes/'.$expired->id.'/accept')->assertUnprocessable();
        $this->assertDatabaseCount('shipments', 1);
    }

    public function test_portal_and_waybill_are_role_protected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $shipment = $this->shipment();
        $this->get('/shipments/'.$shipment->id.'/waybill')->assertRedirect('/login');
        $user = User::create(['email' => 'customer@example.test', 'password' => 'test-password', 'role' => 'customer', 'status' => 'active']);
        $this->actingAs($user)->get('/shipments')->assertForbidden();
    }

    public function test_public_tracking_accepts_lowercase_and_does_not_expose_contacts(): void
    {
        $shipment = $this->shipment(['recipient_email' => 'private@example.test', 'notes' => 'Internal confidential note']);
        $this->get('/track?number='.strtolower($shipment->tracking_number))->assertOk()->assertSee($shipment->tracking_number)->assertDontSee('private@example.test')->assertDontSee('Internal confidential note');
    }

    public function test_dashboard_attention_queue_and_public_pages_render(): void
    {
        $this->actingAs($this->admin());
        foreach (['/dashboard', '/shipments?attention=1', '/shipments/create', '/quotes', '/customers', '/invoices', '/routes', '/notifications', '/admin', '/', '/services', '/quote', '/track'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_saved_quote_has_expiry_and_matches_the_estimate(): void
    {
        $data = ['service_type' => 'express', 'origin_city' => 'Lagos', 'origin_country' => 'Nigeria', 'destination_city' => 'London', 'destination_country' => 'United Kingdom', 'weight' => 8, 'dimensions' => '40x30x50'];
        $this->post('/quote', $data)->assertRedirect('/quote');
        $quote = Quote::firstOrFail();
        $rate = app(RateEngine::class)->quote('express', 8, '40x30x50', 'Nigeria', 'United Kingdom');
        $this->assertEquals($rate['cost'], $quote->estimated_cost);
        $this->assertTrue($quote->expires_at->isFuture());
        $this->assertSame(7, (int) round(now()->diffInDays($quote->expires_at)));
    }

    public function test_booking_requires_real_address_and_positive_weight(): void
    {
        $this->actingAs($this->admin())->postJson('/shipments', $this->payload(['origin_address' => '', 'weight' => 0]))->assertUnprocessable()->assertJsonValidationErrors(['origin_address', 'weight']);
        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseCount('tracking_events', 0);
    }

    public function test_registration_normalizes_email_before_duplicate_check(): void
    {
        User::create(['email' => 'existing@example.test', 'password' => 'test-password', 'role' => 'customer', 'status' => 'active']);
        $this->postJson('/register', ['email' => 'EXISTING@EXAMPLE.TEST', 'password' => 'test-password'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', ['email' => 'invalid@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/login', ['email' => 'invalid@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_retired_service_cannot_be_booked_quoted_or_added_as_a_route(): void
    {
        $this->actingAs($this->admin());
        $data=$this->payload(['service_type'=>'overnight']);
        $this->postJson('/shipments',$data)->assertUnprocessable()->assertJsonValidationErrors('service_type');
        $this->postJson('/quote',$data)->assertUnprocessable()->assertJsonValidationErrors('service_type');
        $this->postJson('/routes',$data+['transit_days'=>1,'base_price'=>15,'currency'=>'USD'])->assertUnprocessable()->assertJsonValidationErrors('service_type');
        $this->get('/services')->assertOk()->assertDontSee('Overnight');
        $this->get('/shipments/create')->assertOk()->assertDontSee('Overnight');
        $this->get('/routes/create')->assertOk()->assertDontSee('Overnight');
    }
    public function test_rate_engine_rejects_retired_service(): void
    {
        $this->expectException(ValidationException::class);
        app(RateEngine::class)->quote('overnight',2,null,'Nigeria','Nigeria');
    }
}
