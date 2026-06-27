@extends('layouts.portal')
@section('title', 'Routes — ShipFast')
@section('heading', 'Routes')
@section('content')
<div class="flex justify-end"><a href="{{ route('routes.create') }}" class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground">New route</a></div>
<div class="mt-4 overflow-hidden rounded-xl border border-border bg-card">
    <table class="w-full text-sm">
        <thead class="border-b border-border bg-secondary/40 text-left font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
            <tr><th class="px-4 py-3">Route</th><th class="px-4 py-3">Service</th><th class="px-4 py-3">Transit</th><th class="px-4 py-3 text-right">Base</th><th class="px-4 py-3">Active</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($routes as $r)
                <tr class="hover:bg-secondary/30">
                    <td class="px-4 py-3">{{ $r->origin_city }} &rarr; {{ $r->destination_city }}</td>
                    <td class="px-4 py-3">{{ ucfirst($r->service_type) }}</td>
                    <td class="px-4 py-3">{{ $r->transit_days }}d</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $r->currency }} {{ number_format((float) $r->base_price, 2) }}</td>
                    <td class="px-4 py-3">@if ($r->active)<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">Active</span>@else<span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">Off</span>@endif</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('routes.edit', $r) }}" class="rounded-lg border border-border px-3 py-1 text-xs font-semibold">Edit</a>
                            <form method="POST" action="{{ route('routes.destroy', $r) }}" onsubmit="return confirm('Delete route?')">@csrf @method('DELETE')<button class="rounded-lg border border-destructive/40 px-3 py-1 text-xs font-semibold text-destructive">Delete</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No routes defined.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $routes->links() }}</div>
@endsection
