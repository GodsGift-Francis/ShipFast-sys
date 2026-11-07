@use('App\Support\Status')
@php($nextStep = Status::nextStep($shipment->status))
@if($nextStep)
<aside class="tracking-next" aria-labelledby="tracking-next-title">
    <span class="tracking-next-mark" aria-hidden="true">↗</span>
    <div>
        <p class="eyebrow">WHAT HAPPENS NEXT</p>
        <h3 id="tracking-next-title">{{ $nextStep['title'] }}</h3>
        <p>{{ $nextStep['description'] }}</p>
    </div>
</aside>
@endif
