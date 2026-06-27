<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Shipment;
use App\Services\RateEngine;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function services()
    {
        return view('public.services');
    }

    public function track(Request $request, ?string $trackingNumber = null)
    {
        $number = $trackingNumber ?: $request->query('number');
        $shipment = null;
        $events = collect();
        $notFound = false;

        if ($number) {
            $shipment = Shipment::where('tracking_number', $number)->first();
            if ($shipment) {
                $events = $shipment->events()->get();
            } else {
                $notFound = true;
            }
        }

        return view('public.track', compact('shipment', 'events', 'number', 'notFound'));
    }

    public function quoteForm()
    {
        return view('public.quote');
    }

    public function storeQuote(Request $request, RateEngine $engine)
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:standard,express,overnight,freight,international'],
            'origin_city' => ['required', 'string', 'max:120'],
            'origin_country' => ['required', 'string', 'max:120'],
            'destination_city' => ['required', 'string', 'max:120'],
            'destination_country' => ['required', 'string', 'max:120'],
            'weight' => ['required', 'numeric', 'min:0.1'],
            'dimensions' => ['nullable', 'string', 'max:60'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email'],
        ]);

        $result = $engine->quote(
            $data['service_type'],
            (float) $data['weight'],
            $data['dimensions'] ?? null,
            $data['origin_country'],
            $data['destination_country'],
        );

        $quote = Quote::create([
            ...$data,
            'estimated_cost' => $result['cost'],
            'estimated_days' => $result['days'],
            'status' => 'pending',
        ]);

        return redirect()->route('quote.form')->with('quote', [
            'cost' => $result['cost'],
            'days' => $result['days'],
            'service' => $data['service_type'],
            'route' => "{$data['origin_city']} → {$data['destination_city']}",
        ]);
    }
}
