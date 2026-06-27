@extends('layouts.portal')
@section('title', ($customer->exists ? 'Edit' : 'New').' customer — ShipFast')
@section('heading', $customer->exists ? 'Edit customer' : 'New customer')
@section('content')
<form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" class="mx-auto max-w-2xl space-y-5 rounded-xl border border-border bg-card p-6">
    @csrf
    @if ($customer->exists) @method('PUT') @endif
    @if ($errors->any())<div class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ $errors->first() }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2"><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name', $customer->name) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required></div>
        <div><label class="text-sm font-medium">Email</label><input name="email" type="email" value="{{ old('email', $customer->email) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
        <div><label class="text-sm font-medium">Phone</label><input name="phone" value="{{ old('phone', $customer->phone) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
        <div><label class="text-sm font-medium">Company</label><input name="company" value="{{ old('company', $customer->company) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
        <div><label class="text-sm font-medium">Tier</label>
            <select name="tier" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                @foreach (['standard','premium','enterprise'] as $t)<option value="{{ $t }}" @selected(old('tier', $customer->tier)===$t)>{{ ucfirst($t) }}</option>@endforeach
            </select>
        </div>
        <div><label class="text-sm font-medium">City</label><input name="city" value="{{ old('city', $customer->city) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
        <div><label class="text-sm font-medium">Country</label><input name="country" value="{{ old('country', $customer->country) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
        <div class="sm:col-span-2"><label class="text-sm font-medium">Address</label><input name="address" value="{{ old('address', $customer->address) }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5"></div>
    </div>
    <div class="flex gap-3"><button class="rounded-lg bg-accent px-5 py-2.5 font-semibold text-accent-foreground">Save</button><a href="{{ route('customers.index') }}" class="rounded-lg border border-border px-5 py-2.5 font-semibold">Cancel</a></div>
</form>
@endsection
