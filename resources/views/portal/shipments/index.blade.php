@extends('layouts.portal')
@section('title', 'Shipments — ShipFast')
@section('heading', 'Shipments')
@section('content')
@use('App\Support\Status')
<div class="workspace-intro shipment-list-header"><div><p class="eyebrow">YOUR SHIPPING WORKSPACE</p><h2>Every shipment. In sight.</h2><p>Find a booking, follow its journey, or start something new.</p></div><a class="button button-orange" href="{{ route('shipments.create') }}">＋ Create shipment</a></div>
<form method="GET" class="filter-bar"><label class="sr-only" for="shipment-search">Search shipments</label><input id="shipment-search" name="q" value="{{ request('q') }}" placeholder="Search tracking number, city or recipient"><label class="sr-only" for="status-filter">Status</label><select id="status-filter" name="status"><option value="">All statuses</option>@foreach(\App\Services\ShipmentStatusService::STATUSES as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ Status::label($status) }}</option>@endforeach</select><label class="filter-toggle"><input type="checkbox" name="attention" value="1" @checked(request()->boolean('attention'))>Needs attention</label><button class="button button-navy">Apply filters</button>@if(request()->hasAny(['q','status','attention']))<a href="{{ route('shipments.index') }}" class="quiet-link">Clear</a>@endif</form>
{{-- Mobile cards --}}
<div class="mt-4 space-y-3 md:hidden">
    @forelse ($shipments as $s)
        <a href="{{ route('shipments.show', $s) }}" class="block rounded-xl border border-border bg-card p-4">
            <div class="flex items-center justify-between">
                <span class="font-mono text-sm font-semibold">{{ $s->tracking_number }}</span>
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($s->status) }}">{{ Status::label($s->status) }}</span>
            </div>
            <div class="mt-1 text-sm text-muted-foreground">{{ $s->origin_city }} &rarr; {{ $s->destination_city }}</div>
            <div class="mt-2 flex justify-between text-xs text-muted-foreground"><span>{{ ucfirst($s->service_type) }}</span><span>${{ number_format((float) $s->shipping_cost, 2) }}</span></div>
        </a>
    @empty
        <p class="rounded-xl border border-border bg-card p-6 text-center text-sm text-muted-foreground">No shipments match these filters. Create a booking or clear your filters to get started.</p>
    @endforelse
</div>

{{-- Desktop table --}}
<div class="mt-4 hidden overflow-hidden rounded-xl border border-border bg-card md:block">
    <table class="w-full text-sm">
        <thead class="border-b border-border bg-secondary/40 text-left font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
            <tr><th class="px-4 py-3">Tracking</th><th class="px-4 py-3">Route</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Cost</th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($shipments as $s)
                <tr class="hover:bg-secondary/30">
                    <td class="px-4 py-3"><a href="{{ route('shipments.show', $s) }}" class="font-mono font-medium hover:text-accent">{{ $s->tracking_number }}</a></td>
                    <td class="px-4 py-3 text-muted-foreground">{{ $s->origin_city }} &rarr; {{ $s->destination_city }}</td>
                    <td class="px-4 py-3">{{ ucfirst($s->service_type) }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($s->status) }}">{{ Status::label($s->status) }}</span></td>
                    <td class="px-4 py-3 text-right font-mono">${{ number_format((float) $s->shipping_cost, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No shipments match these filters. Create a booking or clear your filters to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $shipments->links() }}</div>
@endsection
