@extends('layouts.portal')
@section('title', 'Dashboard — ShipFast')
@section('heading', 'Dashboard')
@section('content')
@use('App\Support\Status')
@php($cards = [
    ['Total shipments', $stats['total']],
    ['Active', $stats['active']],
    ['Delivered today', $stats['delivered_today']],
    ['Monthly revenue', '$'.number_format($stats['monthly_revenue']/1000, 1).'k'],
])
<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    @foreach ($cards as [$label, $value])
        <div class="relative overflow-hidden rounded-xl border border-border bg-card p-4 transition hover:-translate-y-1 hover:shadow-lg">
            <span class="absolute left-0 top-0 h-full w-1 bg-accent"></span>
            <div class="font-mono text-[11px] font-bold uppercase tracking-wider text-muted-foreground">{{ $label }}</div>
            <div class="mt-1 font-display text-3xl font-extrabold">{{ $value }}</div>
        </div>
    @endforeach
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="rounded-xl border border-border bg-card p-6 lg:col-span-2">
        <h2 class="font-display text-lg font-bold">Shipments by status</h2>
        <div class="mt-4 h-72"><canvas id="statusChart"></canvas></div>
    </div>
    <div class="rounded-xl border border-border bg-card p-6">
        <h2 class="font-display text-lg font-bold">Recent shipments</h2>
        <ul class="mt-4 divide-y divide-border">
            @forelse ($recent as $s)
                <li class="flex items-center justify-between gap-2 py-3">
                    <div class="min-w-0">
                        <a href="{{ route('shipments.show', $s) }}" class="block truncate font-mono text-sm font-medium hover:text-accent">{{ $s->tracking_number }}</a>
                        <div class="truncate text-xs text-muted-foreground">{{ $s->origin_city }} &rarr; {{ $s->destination_city }}</div>
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($s->status) }}">{{ Status::label($s->status) }}</span>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-muted-foreground">No shipments yet. <a href="{{ route('shipments.create') }}" class="text-accent">Create one</a>.</li>
            @endforelse
        </ul>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('statusChart');
    if (ctx) new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{ label: 'Shipments', data: @json($chartData), backgroundColor: 'hsl(35 95% 55%)', borderRadius: 6 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
</script>
@endpush
@endsection
