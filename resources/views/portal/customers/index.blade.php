@extends('layouts.portal')
@section('title', 'Customers — ShipFast')
@section('heading', 'Customers')
@section('content')
<div class="workspace-intro"><div><p class="eyebrow">SHIPFAST / OPERATIONS</p><h2>The people behind every parcel.</h2><p>Keep customer details close, from first booking to the next.</p></div></div>
@use('App\Support\Status')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex flex-1 gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="min-w-0 flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm">
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Search</button>
    </form>
    <a href="{{ route('customers.create') }}" class="rounded-lg bg-accent px-4 py-2 text-center text-sm font-semibold text-accent-foreground">New customer</a>
</div>
<div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($customers as $c)
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="font-display font-bold">{{ $c->name }}</div>
                    <div class="text-xs text-muted-foreground">{{ $c->company ?: $c->email ?: '—' }}</div>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($c->tier) }}">{{ ucfirst($c->tier) }}</span>
            </div>
            <div class="mt-4 flex items-center justify-between text-xs text-muted-foreground">
                <span>{{ $c->total_shipments }} shipments</span>
                <span class="font-mono">${{ number_format((float) $c->total_spend, 0) }}</span>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('customers.edit', $c) }}" class="flex-1 rounded-lg border border-border py-1.5 text-center text-xs font-semibold">Edit</a>
                <form method="POST" action="{{ route('customers.destroy', $c) }}" onsubmit="return confirm('Delete this customer?')">@csrf @method('DELETE')
                    <button class="rounded-lg border border-destructive/40 px-3 py-1.5 text-xs font-semibold text-destructive">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-border bg-card p-6 text-center text-sm text-muted-foreground sm:col-span-2 lg:col-span-3">No customers yet.</p>
    @endforelse
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
