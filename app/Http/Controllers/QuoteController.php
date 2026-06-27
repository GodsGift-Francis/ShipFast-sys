<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Shipment;
use App\Services\ShipmentStatusService;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __construct(private ShipmentStatusService $status) {}

    public function index(Request $request)
    {
        $quotes = Quote::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('portal.quotes.index', compact('quotes'));
    }

    public function accept(Quote $quote)
    {
        if ($quote->status === 'accepted') {
            return back()->with('status', 'Quote already accepted.');
        }

        $shipment = Shipment::create([
            'tracking_number' => ShipmentStatusService::trackingNumber(),
            'status' => 'pending',
            'service_type' => $quote->service_type,
            'origin_city' => $quote->origin_city,
            'origin_country' => $quote->origin_country,
            'destination_city' => $quote->destination_city,
            'destination_country' => $quote->destination_country,
            'recipient_name' => $quote->contact_name,
            'recipient_email' => $quote->contact_email,
            'weight' => $quote->weight,
            'dimensions' => $quote->dimensions,
            'shipping_cost' => $quote->estimated_cost,
            'estimated_delivery' => now()->addDays((int) ($quote->estimated_days ?? 3)),
        ]);

        $this->status->apply($shipment, 'pending', "{$shipment->origin_city}, {$shipment->origin_country}", 'Created from quote #'.$quote->id, notify: false);

        $quote->update(['status' => 'accepted']);

        return redirect()->route('shipments.show', $shipment)->with('status', 'Quote accepted — shipment '.$shipment->tracking_number.' created.');
    }
}
