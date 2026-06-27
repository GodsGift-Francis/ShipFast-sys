@extends('layouts.public')
@section('title', 'Track — ShipFast')
@section('content')
@use('App\Support\Status')
<section class="bg-primary py-14 text-primary-foreground">
    <div class="waybill-grid absolute inset-0 opacity-20"></div>
    <div class="container relative mx-auto max-w-2xl px-4">
        <h1 class="font-display text-fluid-title font-extrabold">Track your shipment</h1>
        <form method="GET" action="{{ route('track.form') }}" class="mt-5 flex gap-2">
            <input name="number" value="{{ $number ?? '' }}" placeholder="Enter tracking number (e.g. SHF-K8F2-2210)"
                   class="flex-1 rounded-lg border border-primary-foreground/20 bg-primary-foreground/10 px-4 py-3 font-mono text-primary-foreground placeholder:text-primary-foreground/40 outline-none focus:ring-2 focus:ring-accent">
            <button class="rounded-lg bg-accent px-5 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Track</button>
        </form>
    </div>
</section>

<section class="bg-background py-12">
    <div class="container mx-auto max-w-2xl px-4">
        @if (!empty($notFound))
            <div class="rounded-xl border border-border bg-card p-8 text-center">
                <p class="font-display text-xl font-bold">No shipment found</p>
                <p class="mt-2 text-muted-foreground">We couldn't find a shipment with number <span class="font-mono">{{ $number }}</span>. Check the number and try again.</p>
            </div>
        @elseif (!empty($shipment))
            <div class="rounded-xl border border-border bg-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-dashed border-border pb-4">
                    <div>
                        <div class="font-mono text-sm font-bold">{{ $shipment->tracking_number }}</div>
                        <div class="mt-1 text-sm text-muted-foreground">{{ $shipment->origin_city }} &rarr; {{ $shipment->destination_city }}</div>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ Status::badge($shipment->status) }}">{{ Status::label($shipment->status) }}</span>
                </div>
                <ol class="mt-6 space-y-5">
                    @forelse ($events as $e)
                        <li class="flex gap-3">
                            <div class="mt-1 flex flex-col items-center">
                                <span class="h-2.5 w-2.5 rounded-full bg-accent"></span>
                                @unless ($loop->last)<span class="mt-1 w-px flex-1 bg-border"></span>@endunless
                            </div>
                            <div class="pb-1">
                                <div class="text-sm font-semibold">{{ Status::label($e->status) }}</div>
                                <div class="text-xs text-muted-foreground">{{ $e->location }} · {{ $e->description }}</div>
                                <div class="mt-0.5 font-mono text-[10px] uppercase tracking-wider text-muted-foreground">{{ $e->timestamp?->format('M d, Y H:i') }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">No tracking events yet.</li>
                    @endforelse
                </ol>
            </div>
        @else
            <p class="text-center text-muted-foreground">Enter a tracking number above to see live status and history.</p>
        @endif
    </div>
</section>
@endsection
