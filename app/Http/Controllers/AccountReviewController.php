<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountReviewController extends Controller
{
    public function index()
    {
        return view('portal.reviews', ['pendingUsers' => User::where('role', 'customer')->whereNull('approved_at')->latest()->get(), 'profileRequests' => ProfileChangeRequest::with('user')->where('status', 'pending')->latest()->get(), 'customers' => Customer::orderBy('name')->get()]);
    }

    public function approve(Request $request, User $user)
    {
        $data = $request->validate(['customer_id' => ['nullable', 'integer', 'exists:customers,id']]);
        DB::transaction(function () use ($user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            if ($user->isSuspended() || $user->role !== 'customer') {
                throw ValidationException::withMessages(['account' => 'Only active customer accounts can be approved.']);
            }if ($user->approved_at) {
                return;
            }
            $customerId = $data['customer_id'] ?? null;
            if ($customerId) {
                $customer = Customer::lockForUpdate()->findOrFail($customerId);
                if (User::where('customer_id', $customerId)->exists()) {
                    throw ValidationException::withMessages(['customer_id' => 'This customer record is already linked to an account.']);
                }
            } else {
                $customer = Customer::create(['name' => $user->name, 'email' => $user->email, 'tier' => 'standard']);
                $customerId = $customer->id;
            }
            $user->update(['approved_at' => now(), 'customer_id' => $customerId]);
            AppNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Your shipping account is ready', 'message' => 'Your account has been approved. Your linked shipments and invoices will appear in your workspace.']);
        });

        return back()->with('status', 'Customer access approved and linked.');
    }

    public function profile(Request $request, ProfileChangeRequest $profileRequest)
    {
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'review_note' => ['nullable', 'string', 'max:500']]);
        DB::transaction(function () use ($request, $profileRequest, $data) {
            $change = ProfileChangeRequest::lockForUpdate()->findOrFail($profileRequest->id);
            if ($change->status !== 'pending') {
                throw ValidationException::withMessages(['review' => 'This request has already been reviewed.']);
            }if ($change->user_id === $request->user()->id) {
                throw ValidationException::withMessages(['review' => 'Another administrator must review your own profile change.']);
            }
            $user = User::lockForUpdate()->findOrFail($change->user_id);
            if ($user->isSuspended()) {
                throw ValidationException::withMessages(['review' => 'A suspended account cannot be reviewed.']);
            }
            if ($data['decision'] === 'approved') {
                if (isset($change->changes['email']) && User::where('email', $change->changes['email'])->whereKeyNot($user->id)->exists()) {
                    throw ValidationException::withMessages(['email' => 'The requested email address is no longer available.']);
                }
                $user->fill(array_intersect_key($change->changes, array_flip(['first_name', 'last_name', 'email'])));
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }$user->save();
            }
            $change->update(['status' => $data['decision'], 'reviewed_by' => $request->user()->id, 'review_note' => $data['review_note'] ?? null, 'reviewed_at' => now()]);
            AppNotification::create(['user_id' => $user->id, 'type' => 'system', 'title' => 'Profile request '.$data['decision'], 'message' => 'Your profile update was '.$data['decision'].(! empty($data['review_note']) ? ' Reviewer note: '.$data['review_note'] : '.')]);
        });

        return back()->with('status','Profile request reviewed.');
    }
}
