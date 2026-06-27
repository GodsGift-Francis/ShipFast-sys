<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Shipment;
use App\Services\RateEngine;
use App\Services\ShipmentStatusService;
use Illuminate\Http\Request;

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
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('portal.shipments.index', compact('shipments'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('portal.shipments.create', compact('customers'));
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

        $shipment = Shipment::create([
            ...$data,
            'tracking_number' => ShipmentStatusService::trackingNumber(),
            'status' => 'pending',
            'shipping_cost' => $result['cost'],
            'estimated_delivery' => now()->addDays($result['days']),
        ]);

        // Seed the first tracking event (no notification on creation).
        $this->status->apply($shipment, 'pending', "{$shipment->origin_city}, {$shipment->origin_country}", 'Shipment booked', notify: false);

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
        ]);

        $this->status->apply($shipment, $data['status'], $data['location'] ?? null, $data['description'] ?? null);

        return back()->with('status', 'Tracking event added.');
    }

    public function cancel(Shipment $shipment)
    {
        if (in_array($shipment->status, ['delivered', 'cancelled'], true)) {
            return back()->with('status', 'This shipment can no longer be cancelled.');
        }
        $this->status->apply($shipment, 'cancelled', null, 'Shipment cancelled by operator');

        return back()->with('status', 'Shipment cancelled.');
    }

    private function validateShipment(Request $request): array
    {
        return $request->validate([
            'service_type' => ['required', 'in:standard,express,overnight,freight,international'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'origin_city' => ['required', 'string', 'max:120'],
            'origin_country' => ['required', 'string', 'max:120'],
            'origin_address' => ['nullable', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:120'],
            'destination_country' => ['required', 'string', 'max:120'],
            'destination_address' => ['nullable', 'string', 'max:255'],
            'sender_name' => ['nullable', 'string', 'max:120'],
            'sender_email' => ['nullable', 'email'],
            'sender_phone' => ['nullable', 'string', 'max:40'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'recipient_email' => ['nullable', 'email'],
            'recipient_phone' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
