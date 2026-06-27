@extends('layouts.public')
@section('title', 'Get a quote — ShipFast')
@section('content')
<section class="bg-primary py-14 text-primary-foreground">
    <div class="waybill-grid absolute inset-0 opacity-20"></div>
    <div class="container relative mx-auto max-w-3xl px-4">
        <h1 class="font-display text-fluid-title font-extrabold">Get an instant quote</h1>
        <p class="mt-3 text-primary-foreground/70">No account required. Pricing reflects dimensional weight, zone, and fuel surcharge.</p>
    </div>
</section>

<section class="bg-background py-12">
    <div class="container mx-auto max-w-3xl px-4">
        @if (session('quote'))
            @php($q = session('quote'))
            <div class="mb-8 overflow-hidden rounded-2xl border border-accent/30 bg-accent/5">
                <div class="flex flex-wrap items-center justify-between gap-4 p-6">
                    <div>
                        <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-accent">Estimated cost · {{ ucfirst($q['service']) }}</p>
                        <p class="mt-1 font-display text-4xl font-extrabold">${{ number_format($q['cost'], 2) }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $q['route'] }} · about {{ $q['days'] }} days</p>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('quote.store') }}" class="space-y-5 rounded-2xl border border-border bg-card p-6">
            @csrf
            @if ($errors->any())
                <div class="rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">{{ $errors->first() }}</div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">Service</label>
                    <select name="service_type" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                        @foreach (['standard'=>'Standard','express'=>'Express','overnight'=>'Overnight','freight'=>'Freight','international'=>'International'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('service_type')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Weight (kg)</label>
                    <input name="weight" type="number" step="0.1" min="0.1" value="{{ old('weight') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Origin city</label>
                    <input name="origin_city" value="{{ old('origin_city') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Origin country</label>
                    <input name="origin_country" value="{{ old('origin_country') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Destination city</label>
                    <input name="destination_city" value="{{ old('destination_city') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Destination country</label>
                    <input name="destination_country" value="{{ old('destination_country') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5" required>
                </div>
                <div>
                    <label class="text-sm font-medium">Dimensions (LxWxH cm, optional)</label>
                    <input name="dimensions" value="{{ old('dimensions') }}" placeholder="30x20x10" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                </div>
                <div>
                    <label class="text-sm font-medium">Contact email (optional)</label>
                    <input name="contact_email" type="email" value="{{ old('contact_email') }}" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5">
                </div>
            </div>
            <button class="rounded-lg bg-accent px-5 py-3 font-semibold text-accent-foreground transition hover:bg-accent/90">Calculate quote</button>
        </form>
    </div>
</section>
@endsection
