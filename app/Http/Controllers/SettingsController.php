<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $pending = ProfileChangeRequest::where('user_id', $request->user()->id)->where('status', 'pending')->latest()->first();

        return view('account.settings', compact('pending'));
    }

    public function profile(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        $data = $request->validate(['first_name' => ['required', 'string', 'max:100'], 'last_name' => ['nullable', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)]]);
        DB::transaction(function () use ($request, $data) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            if (ProfileChangeRequest::where('user_id', $user->id)->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['profile' => 'A profile update is already awaiting review.']);
            }
            $changes = array_filter($data, fn ($value, $key) => ($user->$key ?? '') !== ($value ?? ''), ARRAY_FILTER_USE_BOTH);
            if (! $changes) {
                throw ValidationException::withMessages(['profile' => 'There are no profile changes to review.']);
            }
            ProfileChangeRequest::create(['user_id' => $user->id, 'changes' => $changes]);
            AppNotification::create(['type' => 'system', 'title' => 'Profile change awaiting review', 'message' => 'A user requested an identity update. Review it in Account reviews.']);
        });

        return back()->with('status', 'Your request is awaiting review. Your current profile stays unchanged until approved.');
    }

    public function preferences(Request $request)
    {
        $data = $request->validate(['theme' => ['required', 'in:light,dark,system'], 'density' => ['required', 'in:comfortable,compact'], 'reduce_motion' => ['nullable', 'boolean']]);
        $data['reduce_motion'] = $request->boolean('reduce_motion');
        $request->user()->update(['preferences' => $data]);

        return back()->with('status', 'Appearance preferences saved.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'string', 'min:'.($request->user()->isAdmin() ? 16 : 12), 'max:72', 'confirmed', 'different:current_password']]);
        $user = $request->user();
        DB::transaction(function () use ($user, $data, $request) {
            $user->forceFill(['password' => Hash::make($data['password']), 'remember_token' => Str::random(60)])->save();
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
            }
        });
        $request->session()->regenerate();

        return back()->with('status', 'Password updated. Other database sessions have been signed out.');
    }

    public function sessions(Request $request)
    {
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
        }$request->user()->forceFill(['remember_token' => Str::random(60)])->save();

        return back()->with('status','Other database sessions have been signed out.');
    }
}
