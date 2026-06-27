<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('portal.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('portal.customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request));
        return redirect()->route('customers.index')->with('status', 'Customer created.');
    }

    public function edit(Customer $customer)
    {
        return view('portal.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request));
        return redirect()->route('customers.index')->with('status', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('status', 'Customer deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'tier' => ['required', 'in:standard,premium,enterprise'],
        ]);
    }
}
