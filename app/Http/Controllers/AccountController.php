<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private function customerId(Request $r): ?int
    {
        return $r->user()->approved_at && ! $r->user()->isSuspended() ? $r->user()->customer_id : null;
    }

    public function index(Request $request)
    {
        $id = $this->customerId($request);
        $shipments = Shipment::where('customer_id', $id)->when(! $id, fn ($q) => $q->whereRaw('1=0'))->latest()->paginate(10);
        $notifications = AppNotification::where('user_id', $request->user()->id)->latest()->limit(10)->get();

        return view('account.dashboard', compact('shipments', 'notifications'));
    }

    public function shipment(Request $request, Shipment $shipment)
    {
        abort_unless($this->customerId($request) && $shipment->customer_id === $this->customerId($request), 404);
        $events = $shipment->events()->get();

        return view('account.shipment', compact('shipment', 'events'));
    }

    private function invoices(Request $request)
    {
        $id = $this->customerId($request);

        return Invoice::where('customer_id', $id)->when(! $id, fn ($q) => $q->whereRaw('1=0'))->where(fn ($q) => $q->whereNull('shipment_id')->orWhereHas('shipment', fn ($s) => $s->where('customer_id', $id)));
    }

    public function billing(Request $request)
    {
        $invoices = $this->invoices($request)->latest()->paginate(10);

        return view('account.invoices', compact('invoices'));
    }

    public function invoice(Request $request, Invoice $invoice)
    {
        abort_unless($this->invoices($request)->whereKey($invoice->id)->exists(), 404);

        return view('account.invoice', compact('invoice'));
    }

    public function read(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read' => true]);

        return back();
    }
}
