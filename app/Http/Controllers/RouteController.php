<?php

namespace App\Http\Controllers;

use App\Models\ShippingRoute;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = ShippingRoute::orderBy('origin_city')->paginate(15);
        return view('portal.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('portal.routes.form', ['route' => new ShippingRoute(['currency' => 'USD', 'active' => true])]);
    }

    public function store(Request $request)
    {
        ShippingRoute::create($this->validated($request));
        return redirect()->route('routes.index')->with('status', 'Route created.');
    }

    public function edit(ShippingRoute $route)
    {
        return view('portal.routes.form', compact('route'));
    }

    public function update(Request $request, ShippingRoute $route)
    {
        $route->update($this->validated($request));
        return redirect()->route('routes.index')->with('status', 'Route updated.');
    }

    public function destroy(ShippingRoute $route)
    {
        $route->delete();
        return back()->with('status', 'Route deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'origin_city' => ['required', 'string', 'max:120'],
            'origin_country' => ['required', 'string', 'max:120'],
            'destination_city' => ['required', 'string', 'max:120'],
            'destination_country' => ['required', 'string', 'max:120'],
            'service_type' => ['required', 'in:standard,express,overnight,freight,international'],
            'transit_days' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:8'],
            'active' => ['nullable', 'boolean'],
        ]) + ['active' => $request->boolean('active')];
    }
}
