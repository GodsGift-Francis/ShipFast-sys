@extends('layouts.app')
@section('body')
@php($nav = [
    ['dashboard','Dashboard','M3 12l9-9 9 9M5 10v10h14V10'],
    ['shipments.index','Shipments','M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
    ['quotes.index','Quotes','M9 12h6m-6 4h6M9 8h6M5 3h14v18H5z'],
    ['customers.index','Customers','M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1a4 4 0 100-8 4 4 0 000 8z'],
    ['invoices.index','Invoices','M9 14l2 2 4-4M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z'],
    ['routes.index','Routes','M9 20l-5-2V4l5 2 6-2 5 2v14l-5-2-6 2zM9 6v14M15 4v14'],
    ['notifications.index','Alerts','M15 17h5l-1.4-1.4A2 2 0 0118 14V11a6 6 0 10-12 0v3a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1'],
])
@php($bottom = [$nav[0], $nav[1], $nav[3], $nav[6]])
<div class="portal-shell flex min-h-screen bg-secondary/30" x-data="{ open: false }">
    {{-- Desktop sidebar --}}
    <aside class="portal-sidebar hidden w-64 flex-col border-r border-border bg-primary text-primary-foreground md:flex">
        <div class="flex h-16 items-center border-b border-primary-foreground/10 px-6">@include('partials.brand')</div>
        @include('partials.portal-nav', ['nav' => $nav])
        @include('partials.portal-user')
    </aside>

    {{-- Mobile drawer --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 md:hidden" style="display:none">
        <div @click="open=false" class="absolute inset-0 bg-black/50"></div>
        <aside x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               class="absolute left-0 top-0 flex h-full w-72 flex-col bg-primary text-primary-foreground shadow-2xl">
            <div class="flex h-16 items-center border-b border-primary-foreground/10 px-6">@include('partials.brand')</div>
            @include('partials.portal-nav', ['nav' => $nav])
            @include('partials.portal-user')
        </aside>
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="portal-topbar sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/85 px-4 backdrop-blur md:px-6">
            <div class="flex items-center gap-2">
                <button @click="open=true" class="md:hidden" aria-label="Open menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="font-display text-lg font-extrabold tracking-tight"><span class="topbar-context">Workspace <span>/</span></span> @yield('heading', 'Operations')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-wider text-positive sm:flex">
                    <span class="h-2 w-2 rounded-full bg-positive"></span> Local workspace
                </span>
                <a href="{{ route('notifications.index') }}" class="relative p-2 text-muted-foreground hover:text-foreground">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14V11a6 6 0 10-12 0v3a2 2 0 01-.6 1.4L4 17h5"/></svg>

                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 pb-24 md:p-6 md:pb-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-positive/30 bg-positive/10 px-4 py-2.5 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())<div role="alert" class="validation-alert"><strong>Please check the following</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>

    {{-- Mobile bottom tab bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-border bg-background/95 backdrop-blur md:hidden" style="padding-bottom: env(safe-area-inset-bottom)">
        @foreach ($bottom as [$route, $label, $path])
            @php($active = request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route)))
            <a href="{{ route($route) }}" class="relative flex flex-col items-center gap-1 py-2.5">
                @if ($active)<span class="absolute top-0 h-0.5 w-10 rounded-full bg-accent"></span>@endif
                <svg class="h-5 w-5 {{ $active ? 'text-accent' : 'text-muted-foreground' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                <span class="text-[10px] font-medium {{ $active ? 'text-foreground' : 'text-muted-foreground' }}">{{ $label }}</span>
            </a>
        @endforeach
    </nav>
</div>
@endsection
