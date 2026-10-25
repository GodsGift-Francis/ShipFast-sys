<?php

namespace App\Services;

use App\Support\Country;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class RateEngine
{
    public static function dimensionRules(): array
    {
        return ['nullable', 'string', 'max:60', 'regex:/^\s*\d+(?:\.\d+)?\s*[x×]\s*\d+(?:\.\d+)?\s*[x×]\s*\d+(?:\.\d+)?\s*$/iu'];
    }

    public function quote(string $serviceType, float $weight, ?string $dimensions, string $originCountry, string $destinationCountry): array
    {
        $rate = config("shipping.services.{$serviceType}");
        if (! $rate) {
            throw ValidationException::withMessages(['service_type'=>'Choose an available shipping service.']);
        }
        if (! is_finite($weight) || $weight <= 0 || $weight > 10000) {
            throw ValidationException::withMessages(['weight' => 'Choose a valid service and enter a weight between 0.01 and 10,000 kg.']);
        }
        $international = Country::international($originCountry, $destinationCountry);

        $dimWeight = 0;
        if ($dimensions) {
            if (! preg_match('/^\s*(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)\s*[x×]\s*(\d+(?:\.\d+)?)\s*$/iu', $dimensions, $m)) {
                throw ValidationException::withMessages(['dimensions' => 'Use length × width × height in cm, for example 30x20x10.']);
            }
            $sides = array_map('floatval', array_slice($m, 1));
            if (min($sides) <= 0 || max($sides) > 1000) {
                throw ValidationException::withMessages(['dimensions' => 'Each dimension must be greater than zero and no more than 1,000 cm.']);
            }
            $dimWeight = array_product($sides) / config('shipping.dimensional_divisor');
        }
        $billable = ceil(max($weight, $dimWeight) * 2) / 2;
        $transport = round(($rate['base'] + $billable * $rate['per_kg']) * ($international ? config('shipping.international_multiplier') : 1), 2);
        $fuel = round($transport * config('shipping.fuel_surcharge'), 2);
        $minimumAdjustment = max(0, round(config('shipping.minimum_charge') - $transport - $fuel, 2));
        $days = $rate['days'] + ($international ? 3 : 0);

        return ['cost' => round($transport + $fuel + $minimumAdjustment, 2), 'days' => $days, 'billableWeight' => $billable, 'actualWeight' => $weight, 'dimensionalWeight' => round($dimWeight, 3), 'transport' => $transport, 'fuel' => $fuel, 'minimumAdjustment' => $minimumAdjustment, 'currency' => config('shipping.currency'), 'international' => $international, 'estimatedDelivery' => $this->deliveryDate($days)->toDateString(), 'notice' => 'ShipFast estimate in USD. Duties and taxes excluded. Weekends excluded; holidays and customs may affect delivery.'];
    }

    public function deliveryDate(int $days): Carbon
    {
        return Carbon::now()->addWeekdays($days)->endOfDay();
    }
}
