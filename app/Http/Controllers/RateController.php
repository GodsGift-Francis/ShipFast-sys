<?php

namespace App\Http\Controllers;

use App\Services\RateEngine;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RateController extends Controller
{
    public function __invoke(Request $request, RateEngine $engine)
    {
        $data = $request->validate(['weight' => ['required', 'numeric', 'min:0.01', 'max:10000'], 'dimensions' => RateEngine::dimensionRules(), 'origin_country' => ['required', 'string', 'max:120'], 'destination_country' => ['required', 'string', 'max:120']]);
        $rates = [];
        $unavailable = [];
        foreach (config('shipping.services') as $key => $service) {
            try {
                $rates[$key] = ['label' => $service['label'], 'description' => $service['description']] + $engine->quote($key, (float) $data['weight'], $data['dimensions'] ?? null, $data['origin_country'], $data['destination_country']);
            } catch (ValidationException $e) {
                if (isset($e->errors()['service_type'])) {
                    $unavailable[$key] = $e->errors()['service_type'][0];
                } else {
                    throw $e;
                }
            }
        }

        return response()->json(['rates' => $rates, 'unavailable' => $unavailable]);
    }
}
