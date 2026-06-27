@extends('layouts.app')
@section('title', 'Sign in — ShipFast')

@section('body')
<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-primary px-4 py-12 text-primary-foreground"
     x-data="{ mode: '{{ $errors->any() && old('_form') === 'register' ? 'register' : 'login' }}' }">
    <div class="waybill-grid absolute inset-0 opacity-30"></div>
    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-accent/15 blur-3xl animate-glow-pulse"></div>

    <div class="relative w-full max-w-md">
        <a href="{{ route('home') }}" class="mb-6 inline-flex items-center gap-2 text-sm text-primary-foreground/60 transition-colors hover:text-primary-foreground">
            &larr; Back to site
        </a>

        <div class="overflow-hidden rounded-2xl border border-primary-foreground/10 bg-card text-card-foreground shadow-2xl shadow-black/40">
            <div class="border-b border-dashed border-border px-6 py-4">@include('partials.brand')</div>

            <div class="grid grid-cols-2 border-b border-border font-mono text-[12px] font-bold uppercase tracking-wider">
                <button type="button" @click="mode='login'" :class="mode==='login' ? 'border-b-2 border-accent text-foreground' : 'text-muted-foreground'" class="py-3 transition-colors">Sign in</button>
                <button type="button" @click="mode='register'" :class="mode==='register' ? 'border-b-2 border-accent text-foreground' : 'text-muted-foreground'" class="py-3 transition-colors">Create account</button>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Sign in --}}
                <form method="POST" action="{{ route('login') }}" x-show="mode==='login'" class="space-y-4">
                    @csrf
                    <div>
                        <h1 class="font-display text-2xl font-extrabold tracking-tight">Welcome back</h1>
                        <p class="mt-1 text-sm text-muted-foreground">Sign in to track shipments and manage operations.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="email">Email</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="password">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40">
                    </div>
                    <button class="w-full rounded-lg bg-accent px-4 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Sign in</button>
                    <p class="text-center text-sm text-muted-foreground">New here? <button type="button" @click="mode='register'" class="font-medium text-foreground underline-offset-2 hover:underline">Create an account</button></p>
                </form>

                {{-- Register --}}
                <form method="POST" action="{{ route('register') }}" x-show="mode==='register'" x-cloak class="space-y-4" style="display:none">
                    @csrf
                    <input type="hidden" name="_form" value="register">
                    <div>
                        <h1 class="font-display text-2xl font-extrabold tracking-tight">Create your account</h1>
                        <p class="mt-1 text-sm text-muted-foreground">Track your shipments and get instant quotes.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium" for="first_name">First name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium" for="last_name">Last name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="remail">Email</label>
                        <input id="remail" name="email" type="email" required value="{{ old('email') }}" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium" for="rpassword">Password</label>
                        <input id="rpassword" name="password" type="password" required class="w-full rounded-lg border border-input bg-background px-3 py-2.5 outline-none focus:border-accent focus:ring-2 focus:ring-accent/40" placeholder="At least 8 characters">
                    </div>
                    <button class="w-full rounded-lg bg-accent px-4 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Create account</button>
                    <p class="text-center text-sm text-muted-foreground">Already have an account? <button type="button" @click="mode='login'" class="font-medium text-foreground underline-offset-2 hover:underline">Sign in</button></p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
