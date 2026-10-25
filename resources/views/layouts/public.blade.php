@extends('layouts.app')

@section('body')
@php($nav = [['Services',route('services')],['Track shipment',route('track.form')],['Get a quote',route('quote.form')]])
<header x-data="{ open: false }" class="public-nav sticky top-0 z-40 border-b border-border bg-background/85 backdrop-blur">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
        @include('partials.brand')

        <nav class="hidden items-center gap-7 md:flex">
            @foreach ($nav as [$label, $href])
                <a href="{{ $href }}" class="text-[13px] font-medium text-muted-foreground transition-colors hover:text-foreground">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('track.form') }}" class="hidden text-[13px] font-medium text-muted-foreground hover:text-foreground sm:block">{{ auth()->user()->isAdmin() ? 'Dashboard' : 'My shipments' }}</a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf<button class="text-[13px] font-medium text-muted-foreground hover:text-foreground">Log out</button></form>
            @else
                <a href="{{ route('login') }}" class="hidden text-[13px] font-medium text-muted-foreground hover:text-foreground sm:block">Log in</a>
            @endauth
            <a href="{{ route('track.form') }}" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition hover:bg-accent/90">Track a package</a>

            <button @click="open = true" class="md:hidden" aria-label="Open menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 md:hidden" style="display:none">
        <div @click="open = false" class="absolute inset-0 bg-black/40"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             class="absolute right-0 top-0 h-full w-72 bg-card p-5 shadow-2xl">
            <div class="flex items-center justify-between">
                @include('partials.brand')
                <button @click="open = false" aria-label="Close"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="mt-6 flex flex-col gap-1">
                @foreach ($nav as [$label, $href])
                    <a href="{{ $href }}" class="rounded-lg px-3 py-3 text-base font-medium hover:bg-secondary">{{ $label }}</a>
                @endforeach
                <a href="{{ route('track.form') }}" class="mt-2 rounded-lg bg-accent px-3 py-3 text-center text-base font-semibold text-accent-foreground">Track a package</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="w-full rounded-lg border border-border px-3 py-3 text-base font-medium">Log out</button></form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-3 text-base font-medium hover:bg-secondary">Log in</a>
                @endauth
            </div>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

@include('partials.footer')
@endsection
