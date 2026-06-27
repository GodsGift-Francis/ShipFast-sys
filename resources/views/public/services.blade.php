@extends('layouts.public')
@section('title', 'Services — ShipFast')
@section('content')
@php($services = [
    ['S-01','Express','Overnight and 2-day priority for time-critical parcels, with guaranteed delivery windows.'],
    ['S-02','Freight','Sea and road freight for pallets and full container loads, optimised for cost per kilo.'],
    ['S-03','International','Customs-cleared cross-border delivery to 190+ countries with full documentation.'],
    ['S-04','Warehousing','Storage, pick-and-pack, and fulfilment from our regional distribution hubs.'],
])
<section class="bg-primary text-primary-foreground">
    <div class="waybill-grid absolute inset-0 opacity-20"></div>
    <div class="container relative mx-auto px-4 py-16">
        <p class="font-mono text-xs font-bold uppercase tracking-[0.2em] text-accent">What we move</p>
        <h1 class="mt-3 font-display text-fluid-hero font-extrabold">Built for every load</h1>
        <p class="mt-4 max-w-xl text-lg text-primary-foreground/70">From a single envelope to a full container, ShipFast moves it with live tracking and proactive alerts.</p>
    </div>
</section>
<section class="bg-background py-16">
    <div class="container mx-auto grid gap-4 px-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($services as [$code, $name, $desc])
            <div class="group rounded-xl border border-border bg-card p-6 transition-all hover:-translate-y-1.5 hover:border-accent/40 hover:shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary/8 font-mono text-xs font-bold text-primary transition-colors group-hover:bg-accent/10 group-hover:text-accent">{{ substr($name,0,2) }}</span>
                    <span class="font-mono text-[11px] font-bold tracking-wider text-muted-foreground">{{ $code }}</span>
                </div>
                <h3 class="mt-5 font-display text-lg font-bold">{{ $name }}</h3>
                <p class="mt-2 text-sm text-muted-foreground">{{ $desc }}</p>
            </div>
        @endforeach
    </div>
    <div class="container mx-auto mt-12 px-4">
        <a href="{{ route('quote.form') }}" class="inline-flex rounded-lg bg-accent px-5 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Get a quote</a>
    </div>
</section>
@endsection
