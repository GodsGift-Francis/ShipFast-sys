<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Shipment;
use App\Services\RateEngine;
use App\Services\ShipmentStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuoteController extends Controller
{
    public function __construct(private ShipmentStatusService $status) {}

    public function index(Request $request)
    {
        $quotes = Quote::query()->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->latest()->paginate(15)->withQueryString();

        return view('portal.quotes.index', compact('quotes'));
    }

    public function accept(Quote $quote, RateEngine $engine)
    {
        $shipment = DB::transaction(function () use ($quote, $engine) {
            $quote = Quote::lockForUpdate()->findOrFail($quote->id);
            if ($quote->shipment_id) {
                return Shipment::findOrFail($quote->shipment_id);
            }
            if ($quote->status !== 'pending' || ($quote->expires_at ?? $quote->created_at->copy()->addDays(config('shipping.quote_valid_days')))->isPast()) {
                throw ValidationException::withMessages(['quote' => 'This quote has expired or is no longer available. Request a new estimate.']);
            }
            if (! array_key_exists($quote->service_type, config('shipping.services'))) {
                throw ValidationException::withMessages(['quote'=>'This service is no longer available. Request a new quote.']);
            }
            // Quotes reserve a price; acceptance creates a draft booking awaiting dispatch details.
            $shipment = Shipment::create(['tracking_number' => ShipmentStatusService::trackingNumber(), 'status' => 'pending', 'service_type' => $quote->service_type, 'origin_city' => $quote->origin_city, 'origin_country' => $quote->origin_country, 'destination_city' => $quote->destination_city, 'destination_country' => $quote->destination_country, 'recipient_name' => $quote->contact_name, 'recipient_email' => $quote->contact_email, 'weight' => $quote->weight, 'dimensions' => $quote->dimensions, 'shipping_cost' => $quote->estimated_cost, 'estimated_delivery' => $engine->deliveryDate((int) ($quote->estimated_days ?? 3)), 'notes' => 'Created from quote #'.$quote->id.'. Confirm address, parcel and customs details before dispatch.']);
            $this->status->apply($shipment, 'pending', "{$shipment->origin_city}, {$shipment->origin_country}", 'Booking created from quote #'.$quote->id, notify: false);
            $quote->update(['status' => 'accepted', 'shipment_id' => $shipment->id]);

            return $shipment;
        });

        return redirect()->route('shipments.show', $shipment)->with('status', 'Quote accepted. Booking '.$shipment->tracking_number.' created. Confirm dispatch details before collection.');
    }
}
