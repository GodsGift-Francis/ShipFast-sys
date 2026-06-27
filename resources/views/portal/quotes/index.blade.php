@extends('layouts.portal')
@section('title', 'Quotes — ShipFast')
@section('heading', 'Quotes')
@section('content')
@use('App\Support\Status')
<div class="overflow-hidden rounded-xl border border-border bg-card">
    <table class="w-full text-sm">
        <thead class="border-b border-border bg-secondary/40 text-left font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
            <tr><th class="px-4 py-3">Route</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3 text-right">Estimate</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($quotes as $q)
                <tr class="hover:bg-secondary/30">
                    <td class="px-4 py-3">{{ $q->origin_city }} &rarr; {{ $q->destination_city }}</td>
                    <td class="px-4 py-3">{{ ucfirst($q->service_type) }}</td>
                    <td class="px-4 py-3 text-muted-foreground">{{ $q->contact_email ?: '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono">${{ number_format((float) $q->estimated_cost, 2) }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($q->status) }}">{{ Status::label($q->status) }}</span></td>
                    <td class="px-4 py-3 text-right">
                        @if ($q->status !== 'accepted')
                            <form method="POST" action="{{ route('quotes.accept', $q) }}">@csrf
                                <button class="rounded-lg bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground">Accept &amp; create</button>
                            </form>
                        @else
                            <span class="text-xs text-muted-foreground">Converted</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No quotes yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $quotes->links() }}</div>
@endsection
