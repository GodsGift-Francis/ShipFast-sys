@extends('layouts.portal')
@section('title', $shipment->tracking_number.' — ShipFast')
@section('heading', 'Shipment')
@section('content')
@use('App\Support\Status')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="font-mono text-xl font-bold">{{ $shipment->tracking_number }}</div>
            <div class="mt-1 text-sm text-muted-foreground">{{ $shipment->origin_city }}, {{ $shipment->origin_country }} &rarr; {{ $shipment->destination_city }}, {{ $shipment->destination_country }}</div>
        </div>
        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ Status::badge($shipment->status) }}">{{ Status::label($shipment->status) }}</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-4">
        @php($meta = [['Service', ucfirst($shipment->service_type)],['Weight', ($shipment->weight ? $shipment->weight.' kg' : '—')],['Cost', '$'.number_format((float) $shipment->shipping_cost, 2)],['ETA', $shipment->estimated_delivery?->format('M d, Y') ?? '—']])
        @foreach ($meta as [$k,$v])
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-muted-foreground">{{ $k }}</div>
                <div class="mt-1 font-semibold">{{ $v }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        {{-- Timeline --}}
        <div class="rounded-xl border border-border bg-card p-6">
            <h2 class="font-display text-lg font-bold">Tracking history</h2>
            <ol class="mt-5 space-y-5">
                @forelse ($shipment->events as $e)
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
                    <li class="text-sm text-muted-foreground">No events yet.</li>
                @endforelse
            </ol>
        </div>

        {{-- Actions --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-border bg-card p-6">
                <h2 class="font-display text-lg font-bold">Update status</h2>
                <form method="POST" action="{{ route('shipments.events', $shipment) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="status" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                        @foreach (\App\Services\ShipmentStatusService::STATUSES as $s)
                            <option value="{{ $s }}" @selected($shipment->status===$s)>{{ Status::label($s) }}</option>
                        @endforeach
                    </select>
                    <input name="location" placeholder="Location (optional)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                    <input name="description" placeholder="Note (optional)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                    <button class="w-full rounded-lg bg-accent px-4 py-2.5 font-semibold text-accent-foreground transition hover:bg-accent/90">Add update &amp; notify</button>
                </form>
                <p class="mt-2 text-xs text-muted-foreground">A status change emails and texts the recipient automatically.</p>
            </div>

            @unless (in_array($shipment->status, ['delivered','cancelled'], true))
                <div class="rounded-xl border border-destructive/20 bg-card p-6" x-data="{ confirm:false }">
                    <h2 class="font-display text-lg font-bold">Cancel shipment</h2>
                    <p class="mt-1 text-sm text-muted-foreground">This stops the shipment and notifies the recipient.</p>
                    <button @click="confirm=true" x-show="!confirm" class="mt-3 rounded-lg border border-destructive/40 px-4 py-2 text-sm font-semibold text-destructive">Cancel shipment</button>
                    <form method="POST" action="{{ route('shipments.cancel', $shipment) }}" x-show="confirm" x-cloak class="mt-3 flex gap-2" style="display:none">
                        @csrf @method('PATCH')
                        <button class="rounded-lg bg-destructive px-4 py-2 text-sm font-semibold text-destructive-foreground">Confirm cancel</button>
                        <button type="button" @click="confirm=false" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold">Keep</button>
                    </form>
                </div>
            @endunless
        </div>
    </div>
</div>
@endsection
