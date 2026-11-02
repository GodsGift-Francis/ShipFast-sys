<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Gate a route by role. Usage: ->middleware('role:admin') or 'role:super_admin'.
     * 'admin' also allows super_admin. Suspended accounts are always rejected.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuspended()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'This account has been suspended.']);
        }

        $allowed = match ($role) {
            'super_admin' => $user->isSuperAdmin(),
            'admin' => $user->isAdmin(),
            'customer' => $user->role === 'customer',
            'account' => true,
            default => false,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
