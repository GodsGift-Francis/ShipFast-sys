@extends('layouts.portal')
@section('title', ($route->exists ? 'Edit' : 'New').' route — ShipFast')
@section('heading', $route->exists ? 'Edit route' : 'New route')
@section('content')
<form method="POST" action="{{ $route->exists ? route('routes.update', $route) : route('routes.store') }}" class="mx-auto max-w-2xl space-y-5 rounded-xl border border-border bg-card p-6">
    @csrf
    @if ($route->exists) @method('PUT') @endif
    @if ($errors->any())<div class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ $errors->first() }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="text-sm font-medium">Origin city</label><input name="origin_city" value="{{ old('origin_city', $route->origin_city) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Origin country</label><input name="origin_country" value="{{ old('origin_country', $route->origin_country) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Destination city</label><input name="destination_city" value="{{ old('destination_city', $route->destination_city) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Destination country</label><input name="destination_country" value="{{ old('destination_country', $route->destination_country) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Service</label>
            <select name="service_type" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">@foreach (array_keys(config('shipping.services')) as $v)<option value="{{ $v }}" @selected(old('service_type', $route->service_type)===$v)>{{ ucfirst($v) }}</option>@endforeach</select>
        </div>
        <div><label class="text-sm font-medium">Transit days</label><input name="transit_days" type="number" min="1" value="{{ old('transit_days', $route->transit_days ?? 1) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Base price</label><input name="base_price" type="number" step="0.01" min="0" value="{{ old('base_price', $route->base_price ?? 0) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Currency</label><input name="currency" value="{{ old('currency', $route->currency ?? 'USD') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <label class="flex items-center gap-2 sm:col-span-2"><input type="checkbox" name="active" value="1" @checked(old('active', $route->active ?? true)) class="rounded border-input text-accent focus:ring-accent"> <span class="text-sm font-medium">Active</span></label>
    </div>
    <div class="flex gap-3"><button class="rounded-lg bg-accent px-5 py-2.5 font-semibold text-accent-foreground">Save</button><a href="{{ route('routes.index') }}" class="rounded-lg border border-border px-5 py-2.5 font-semibold">Cancel</a></div>
</form>
@endsection
