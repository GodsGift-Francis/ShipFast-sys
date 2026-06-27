@extends('layouts.portal')
@section('title', ($invoice->exists ? 'Edit' : 'New').' invoice — ShipFast')
@section('heading', $invoice->exists ? 'Edit invoice' : 'New invoice')
@section('content')
<form method="POST" action="{{ $invoice->exists ? route('invoices.update', $invoice) : route('invoices.store') }}" class="mx-auto max-w-2xl space-y-5 rounded-xl border border-border bg-card p-6">
    @csrf
    @if ($invoice->exists) @method('PUT') @endif
    @if ($errors->any())<div class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ $errors->first() }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="text-sm font-medium">Customer</label>
            <select name="customer_id" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"><option value="">— None —</option>
                @foreach ($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id', $invoice->customer_id)==$c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">Shipment</label>
            <select name="shipment_id" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"><option value="">— None —</option>
                @foreach ($shipments as $s)<option value="{{ $s->id }}" @selected(old('shipment_id', $invoice->shipment_id)==$s->id)>{{ $s->tracking_number }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">Amount</label><input name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $invoice->amount ?? 0) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Currency</label><input name="currency" value="{{ old('currency', $invoice->currency ?? 'USD') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Status</label>
            <select name="status" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">@foreach (['draft','sent','paid','overdue','cancelled'] as $s)<option value="{{ $s }}" @selected(old('status', $invoice->status)===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
        </div>
        <div><label class="text-sm font-medium">Due date</label><input name="due_date" type="date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
    </div>
    <div class="flex gap-3"><button class="rounded-lg bg-accent px-5 py-2.5 font-semibold text-accent-foreground">Save</button><a href="{{ route('invoices.index') }}" class="rounded-lg border border-border px-5 py-2.5 font-semibold">Cancel</a></div>
</form>
@endsection
