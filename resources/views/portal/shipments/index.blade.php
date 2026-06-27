@extends('layouts.portal')
@section('title', 'Shipments — ShipFast')
@section('heading', 'Shipments')
@section('content')
@use('App\Support\Status')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex flex-1 flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Search tracking, city, recipient" class="min-w-0 flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-input bg-background px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Services\ShipmentStatusService::STATUSES as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ Status::label($s) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Search</button>
    </form>
    <a href="{{ route('shipments.create') }}" class="rounded-lg bg-accent px-4 py-2 text-center text-sm font-semibold text-accent-foreground">New shipment</a>
</div>

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
        <p class="rounded-xl border border-border bg-card p-6 text-center text-sm text-muted-foreground">No shipments found.</p>
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
                <tr><td colspan="5" class="px-4 py-8 text-center text-muted-foreground">No shipments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $shipments->links() }}</div>
@endsection
