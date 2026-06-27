@extends('layouts.portal')
@section('title', 'Alerts — ShipFast')
@section('heading', 'Alerts & delivery log')
@section('content')
@php($channelBadge = fn($s) => match($s){'sent'=>'bg-emerald-100 text-emerald-800','failed'=>'bg-red-100 text-red-800',default=>'bg-gray-100 text-gray-700'})
<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-border bg-card p-6">
        <h2 class="font-display text-lg font-bold">Notifications</h2>
        <ul class="mt-4 divide-y divide-border">
            @forelse ($notifications as $n)
                <li class="py-3">
                    <div class="flex items-center justify-between"><span class="text-sm font-semibold">{{ $n->title }}</span>@unless ($n->read)<span class="h-2 w-2 rounded-full bg-accent"></span>@endunless</div>
                    <p class="mt-0.5 text-sm text-muted-foreground">{{ $n->message }}</p>
                    <div class="mt-1 font-mono text-[10px] uppercase tracking-wider text-muted-foreground">{{ $n->created_at?->diffForHumans() }}</div>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-muted-foreground">No notifications.</li>
            @endforelse
        </ul>
    </div>

    <div class="rounded-xl border border-border bg-card p-6">
        <h2 class="font-display text-lg font-bold">Delivery log</h2>
        <p class="mt-1 text-xs text-muted-foreground">Every email/SMS attempt from status changes is recorded here.</p>
        <ul class="mt-4 divide-y divide-border">
            @forelse ($deliveries as $d)
                <li class="flex items-center justify-between gap-2 py-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-muted-foreground">{{ $d->channel }}</span>
                            <span class="truncate text-sm">{{ $d->recipient ?: '—' }}</span>
                        </div>
                        <div class="truncate text-xs text-muted-foreground">{{ $d->shipment?->tracking_number }} · {{ $d->status_to ? \App\Support\Status::label($d->status_to) : '' }}</div>
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $channelBadge($d->status) }}">{{ ucfirst($d->status) }}</span>
                </li>
            @empty
                <li class="py-6 text-center text-sm text-muted-foreground">No delivery attempts yet.</li>
            @endforelse
        </ul>
        <div class="mt-4">{{ $deliveries->links() }}</div>
    </div>
</div>
@endsection
