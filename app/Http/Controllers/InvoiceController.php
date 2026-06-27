<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        return view('portal.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('portal.invoices.form', [
            'invoice' => new Invoice(['currency' => 'USD', 'status' => 'draft']),
            'customers' => Customer::orderBy('name')->get(),
            'shipments' => Shipment::latest()->limit(100)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['invoice_number'] = 'INV-'.strtoupper(Str::random(6));
        Invoice::create($data);

        return redirect()->route('invoices.index')->with('status', 'Invoice created.');
    }

    public function edit(Invoice $invoice)
    {
        return view('portal.invoices.form', [
            'invoice' => $invoice,
            'customers' => Customer::orderBy('name')->get(),
            'shipments' => Shipment::latest()->limit(100)->get(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->update($this->validated($request));
        return redirect()->route('invoices.index')->with('status', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('status', 'Invoice deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'shipment_id' => ['nullable', 'exists:shipments,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:8'],
            'status' => ['required', 'in:draft,sent,paid,overdue,cancelled'],
            'due_date' => ['nullable', 'date'],
        ]);
    }
}
