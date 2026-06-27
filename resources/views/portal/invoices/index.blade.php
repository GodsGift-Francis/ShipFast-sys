@extends('layouts.portal')
@section('title', 'Invoices — ShipFast')
@section('heading', 'Invoices')
@section('content')
@use('App\Support\Status')
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex gap-2">
        <select name="status" class="rounded-lg border border-input bg-background px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (['draft','sent','paid','overdue','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach
        </select>
    </form>
    <a href="{{ route('invoices.create') }}" class="rounded-lg bg-accent px-4 py-2 text-center text-sm font-semibold text-accent-foreground">New invoice</a>
</div>
<div class="mt-4 overflow-hidden rounded-xl border border-border bg-card">
    <table class="w-full text-sm">
        <thead class="border-b border-border bg-secondary/40 text-left font-mono text-[11px] uppercase tracking-wider text-muted-foreground">
            <tr><th class="px-4 py-3">Invoice</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3 text-right">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Due</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($invoices as $inv)
                <tr class="hover:bg-secondary/30">
                    <td class="px-4 py-3 font-mono font-medium">{{ $inv->invoice_number }}</td>
                    <td class="px-4 py-3 text-muted-foreground">{{ $inv->customer?->name ?: '—' }}</td>
                    <td class="px-4 py-3 text-right font-mono">{{ $inv->currency }} {{ number_format((float) $inv->amount, 2) }}</td>
                    <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ Status::badge($inv->status) }}">{{ ucfirst($inv->status) }}</span></td>
                    <td class="px-4 py-3 text-muted-foreground">{{ $inv->due_date?->format('M d, Y') ?: '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('invoices.edit', $inv) }}" class="rounded-lg border border-border px-3 py-1 text-xs font-semibold">Edit</a>
                            <form method="POST" action="{{ route('invoices.destroy', $inv) }}" onsubmit="return confirm('Delete invoice?')">@csrf @method('DELETE')<button class="rounded-lg border border-destructive/40 px-3 py-1 text-xs font-semibold text-destructive">Delete</button></form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No invoices yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $invoices->links() }}</div>
@endsection
