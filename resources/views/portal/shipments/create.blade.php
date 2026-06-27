@extends('layouts.portal')
@section('title', 'New shipment — ShipFast')
@section('heading', 'New shipment')
@section('content')
<form method="POST" action="{{ route('shipments.store') }}" class="mx-auto max-w-3xl space-y-6">
    @csrf
    @if ($errors->any())
        <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-xl border border-border bg-card p-6">
        <h2 class="font-display text-lg font-bold">Service</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-sm font-medium">Service type</label>
                <select name="service_type" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                    @foreach (['standard','express','overnight','freight','international'] as $v)
                        <option value="{{ $v }}" @selected(old('service_type')===$v)>{{ ucfirst($v) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Customer (optional)</label>
                <select name="customer_id" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                    <option value="">— None —</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" @selected(old('customer_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl border border-border bg-card p-6">
            <h2 class="font-display text-lg font-bold">Origin</h2>
            <div class="mt-4 space-y-3">
                <input name="origin_city" value="{{ old('origin_city') }}" placeholder="City" class="w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                <input name="origin_country" value="{{ old('origin_country') }}" placeholder="Country" class="w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                <input name="origin_address" value="{{ old('origin_address') }}" placeholder="Address (optional)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="sender_name" value="{{ old('sender_name') }}" placeholder="Sender name" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="sender_email" type="email" value="{{ old('sender_email') }}" placeholder="Sender email" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="sender_phone" value="{{ old('sender_phone') }}" placeholder="Sender phone" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
            </div>
        </div>
        <div class="rounded-xl border border-border bg-card p-6">
            <h2 class="font-display text-lg font-bold">Destination</h2>
            <div class="mt-4 space-y-3">
                <input name="destination_city" value="{{ old('destination_city') }}" placeholder="City" class="w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                <input name="destination_country" value="{{ old('destination_country') }}" placeholder="Country" class="w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                <input name="destination_address" value="{{ old('destination_address') }}" placeholder="Address (optional)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Recipient name" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="recipient_email" type="email" value="{{ old('recipient_email') }}" placeholder="Recipient email" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
                <input name="recipient_phone" value="{{ old('recipient_phone') }}" placeholder="Recipient phone" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-border bg-card p-6">
        <h2 class="font-display text-lg font-bold">Parcel</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <input name="weight" type="number" step="0.1" min="0" value="{{ old('weight') }}" placeholder="Weight (kg)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
            <input name="dimensions" value="{{ old('dimensions') }}" placeholder="Dimensions LxWxH cm (optional)" class="w-full rounded-lg border border-input bg-background px-3 py-2.5">
            <textarea name="notes" placeholder="Notes (optional)" rows="2" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 sm:col-span-2">{{ old('notes') }}</textarea>
        </div>
        <p class="mt-3 text-xs text-muted-foreground">Shipping cost and ETA are calculated automatically from the rate engine.</p>
    </div>

    <div class="flex gap-3">
        <button class="rounded-lg bg-accent px-5 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Create shipment</button>
        <a href="{{ route('shipments.index') }}" class="rounded-lg border border-border px-5 py-3 font-semibold">Cancel</a>
    </div>
</form>
@endsection
