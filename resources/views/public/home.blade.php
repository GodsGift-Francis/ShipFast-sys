@extends('layouts.public')

@section('title', 'ShipFast — Global shipping & logistics')

@section('content')
<section class="relative overflow-hidden bg-primary text-primary-foreground">
    <div class="waybill-grid absolute inset-0 opacity-40"></div>
    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-accent/15 blur-3xl animate-glow-pulse"></div>
    <div class="container relative mx-auto grid items-center gap-12 px-4 py-20 lg:grid-cols-[1.05fr_0.95fr] lg:py-28">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-primary-foreground/15 bg-primary-foreground/5 px-3 py-1 font-mono text-xs font-bold uppercase tracking-[0.2em] text-accent">
                <span class="h-1.5 w-1.5 rounded-full bg-accent animate-transit-pulse"></span>
                Global freight &amp; parcel
            </p>
            <h1 class="mt-5 font-display text-fluid-hero font-extrabold animate-reveal">
                <span class="block">Know where</span>
                <span class="block">it is. <span class="text-accent">Every mile.</span></span>
            </h1>
            <p class="mt-6 max-w-md text-lg text-primary-foreground/70">
                Track parcels and freight in real time, get instant quotes, and ship worldwide — with a status alert at every step.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('quote.form') }}" class="group relative overflow-hidden rounded-lg bg-accent px-5 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Get a quote</a>
                <a href="{{ route('services') }}" class="rounded-lg border border-primary-foreground/25 px-5 py-3 font-semibold text-primary-foreground transition hover:bg-primary-foreground/10">Explore services</a>
            </div>
        </div>

        {{-- Waybill track panel --}}
        <div class="rounded-2xl bg-card p-1.5 text-card-foreground shadow-2xl shadow-black/40 ring-1 ring-black/5 animate-reveal">
            <div class="relative overflow-hidden rounded-[12px] border border-border bg-background">
                <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-12 bg-gradient-to-b from-accent/20 to-transparent animate-scanline"></div>
                <div class="flex items-center justify-between border-b border-dashed border-border px-5 py-3">
                    <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Air Waybill · Track</span>
                    <span class="font-mono text-[11px] font-bold tracking-wider text-accent">SHF</span>
                </div>
                <form method="GET" action="{{ route('track.form') }}" class="p-5">
                    <label for="track" class="font-mono text-[11px] uppercase tracking-wider text-muted-foreground">Tracking number</label>
                    <input id="track" name="number" placeholder="SHF-K8F2-2210"
                           class="mt-2 w-full rounded-lg border border-input bg-secondary/40 px-4 py-3 font-mono text-lg tracking-wide outline-none transition focus:border-accent focus:ring-2 focus:ring-accent/40">
                    <button type="submit" class="mt-3 w-full rounded-lg bg-accent px-4 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Track package</button>
                    <div class="mt-6">
                        <svg viewBox="0 0 320 26" class="w-full" aria-label="route">
                            <line x1="14" y1="13" x2="306" y2="13" stroke="hsl(var(--border))" stroke-width="2" />
                            <line x1="14" y1="13" x2="306" y2="13" stroke="hsl(var(--accent))" stroke-width="2" stroke-dasharray="6 8" class="animate-route-dash" />
                            <circle cx="14" cy="13" r="5" fill="hsl(var(--primary))" />
                            <circle cx="306" cy="13" r="5" fill="hsl(var(--accent))" class="animate-transit-pulse" />
                        </svg>
                        <div class="mt-1 flex justify-between font-mono text-[10px] uppercase tracking-wider text-muted-foreground">
                            <span>Origin</span><span>Destination</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="border-b border-border bg-background py-16">
    <div class="container mx-auto grid gap-8 px-4 sm:grid-cols-3">
        <div>
            <p class="font-mono text-xs font-bold uppercase tracking-[0.2em] text-accent">Live tracking</p>
            <h3 class="mt-2 font-display text-xl font-bold">Every status, in real time</h3>
            <p class="mt-2 text-sm text-muted-foreground">Follow each parcel from booking to delivery, with a timeline of every scan along the way.</p>
        </div>
        <div>
            <p class="font-mono text-xs font-bold uppercase tracking-[0.2em] text-accent">Instant quotes</p>
            <h3 class="mt-2 font-display text-xl font-bold">Transparent pricing</h3>
            <p class="mt-2 text-sm text-muted-foreground">Dimensional weight, zone, and fuel surcharge worked out instantly. No account required.</p>
        </div>
        <div>
            <p class="font-mono text-xs font-bold uppercase tracking-[0.2em] text-accent">Proactive alerts</p>
            <h3 class="mt-2 font-display text-xl font-bold">Email and SMS updates</h3>
            <p class="mt-2 text-sm text-muted-foreground">Recipients get notified automatically the moment a shipment changes status.</p>
        </div>
    </div>
</section>
@endsection
