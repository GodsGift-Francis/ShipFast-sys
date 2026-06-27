<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($q) => $q->where('email', 'like', "%{$request->q}%")
                ->orWhere('first_name', 'like', "%{$request->q}%")
                ->orWhere('last_name', 'like', "%{$request->q}%"))
            ->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:super_admin,admin,customer'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        $this->guardSelf($user, 'modify');

        // A super_admin cannot be suspended.
        if ($user->isSuperAdmin() && $data['status'] === 'suspended') {
            throw ValidationException::withMessages(['status' => 'Super admins cannot be suspended.']);
        }

        // Don't allow removing the last super admin.
        if ($user->isSuperAdmin() && $data['role'] !== 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'You cannot demote the last super admin.']);
        }

        $user->update($data);

        return back()->with('status', "Updated {$user->email}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate(['password' => ['required', 'string', 'min:8']]);
        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', "Password reset for {$user->email}.");
    }

    public function destroy(User $user)
    {
        $this->guardSelf($user, 'delete');

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            throw ValidationException::withMessages(['user' => 'You cannot delete the last super admin.']);
        }

        $user->delete();

        return back()->with('status', 'User deleted.');
    }

    /** Operators cannot suspend, demote, or delete their own account. */
    private function guardSelf(User $user, string $action): void
    {
        if (auth()->id() === $user->id) {
            throw ValidationException::withMessages(['user' => "You cannot {$action} your own account."]);
        }
    }
}
