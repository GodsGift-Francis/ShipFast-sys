<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Shipment;
use App\Services\RateEngine;
use App\Services\ShipmentStatusService;
use App\Support\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function __construct(private ShipmentStatusService $status) {}

    public function index(Request $request)
    {
        $shipments = Shipment::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = trim($request->q);
                $query->where(function ($w) use ($q) {
                    $w->where('tracking_number', 'like', "%{$q}%")
                        ->orWhere('origin_city', 'like', "%{$q}%")
                        ->orWhere('destination_city', 'like', "%{$q}%")
                        ->orWhere('recipient_name', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->boolean('attention'), fn ($query) => $query->where(function ($q) {
                $q->where('status', 'on_hold')->orWhere(function ($q) {
                    $q->whereNotIn('status', ['delivered', 'cancelled'])->where('estimated_delivery', '<', now());
                });
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('portal.shipments.index', compact('shipments'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('portal.shipments.create', ['customers' => $customers, 'shipment' => new Shipment]);
    }

    public function edit(Shipment $shipment)
    {
        abort_unless($shipment->status === 'pending', 403, 'Only uncollected bookings can be edited.');

        return view('portal.shipments.create', ['shipment' => $shipment, 'customers' => Customer::orderBy('name')->get()]);
    }

    public function update(Request $request, Shipment $shipment, RateEngine $engine)
    {
        $data = $this->validateShipment($request);
        DB::transaction(function () use ($data, $shipment, $engine) {
            $locked = Shipment::lockForUpdate()->findOrFail($shipment->id);
            abort_unless($locked->status === 'pending', 403, 'Only uncollected bookings can be edited.');
            $rate = $engine->quote($data['service_type'], (float) $data['weight'], $data['dimensions'] ?? null, $data['origin_country'], $data['destination_country']);
            $locked->update([...$data, 'shipping_cost' => $rate['cost'], 'rate_breakdown' => $rate, 'estimated_delivery' => $engine->deliveryDate($rate['days'])]);
        });

        return redirect()->route('shipments.show', $shipment)->with('status', 'Booking details and shipping estimate updated.');
    }

    public function waybill(Shipment $shipment)
    {
        return view('portal.shipments.waybill', compact('shipment'));
    }

    public function store(Request $request, RateEngine $engine)
    {
        $data = $this->validateShipment($request);

        $result = $engine->quote(
            $data['service_type'],
            (float) ($data['weight'] ?? 1),
            $data['dimensions'] ?? null,
            $data['origin_country'],
            $data['destination_country'],
        );

        $shipment = DB::transaction(function () use ($data, $result, $engine) {
            $shipment = Shipment::create([
                ...$data,
                'tracking_number' => ShipmentStatusService::trackingNumber(),
                'status' => 'pending',
                'shipping_cost' => $result['cost'],
                'estimated_delivery' => $engine->deliveryDate($result['days']),
                'rate_breakdown' => $result,
            ]);

            // Seed the first tracking event (no notification on creation).
            $this->status->apply($shipment, 'pending', "{$shipment->origin_city}, {$shipment->origin_country}", 'Shipment booked', notify: false);

            return $shipment;
        });

        return redirect()->route('shipments.show', $shipment)->with('status', 'Shipment created: '.$shipment->tracking_number);
    }

    public function show(Shipment $shipment)
    {
        $shipment->load('customer', 'events');

        return view('portal.shipments.show', compact('shipment'));
    }

    public function addEvent(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:255'],
            'delivered_to' => ['nullable', 'string', 'max:120'],
        ]);

        $this->status->apply($shipment, $data['status'], $data['location'] ?? null, $data['description'] ?? null, deliveredTo: $data['delivered_to'] ?? null);

        return back()->with('status', 'Tracking event added.');
    }

    public function cancel(Shipment $shipment)
    {
        if (! in_array('cancelled', ShipmentStatusService::allowed($shipment->status), true)) {
            return back()->with('status', 'This shipment can no longer be cancelled.');
        }
        $this->status->apply($shipment, 'cancelled', null, 'Shipment cancelled by operator');

        return back()->with('status', 'Shipment cancelled.');
    }

    private function validateShipment(Request $request): array
    {
        $data = $request->validate([
            'service_type' => ['required', 'in:standard,express,freight,international'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'origin_city' => ['required', 'string', 'max:120'],
            'origin_country' => ['required', 'string', 'max:120'],
            'origin_address' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:120'],
            'destination_country' => ['required', 'string', 'max:120'],
            'destination_address' => ['required', 'string', 'max:255'],
            'sender_name' => ['required', 'string', 'max:120'],
            'sender_email' => ['nullable', 'email'],
            'sender_phone' => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:10000'],
            'dimensions' => RateEngine::dimensionRules(),
            'origin_postal_code' => ['nullable', 'string', 'max:30'],
            'destination_postal_code' => ['nullable', 'string', 'max:30'],
            'contents_description' => ['required', 'string', 'max:255'],
            'declared_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'hs_code' => ['nullable', 'regex:/^\d{6,10}$/'],
            'country_of_manufacture' => ['nullable', 'string', 'max:120'],
            'signature_required' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        if (Country::international($data['origin_country'], $data['destination_country'])) {
            $request->validate(['declared_value' => ['required', 'numeric', 'min:0.01'], 'country_of_manufacture' => ['required', 'string', 'max:120']]);
        }

        return $data + ['signature_required' => false];
    }
}
