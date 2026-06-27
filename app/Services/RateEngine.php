<?php

namespace App\Services;

class RateEngine
{
    /** Per-service base charge, per-kg rate, and base transit days. */
    private const RATES = [
        'standard'      => ['base' => 4,  'perKg' => 1.8, 'days' => 3],
        'express'       => ['base' => 9,  'perKg' => 3.5, 'days' => 2],
        'overnight'     => ['base' => 15, 'perKg' => 5.0, 'days' => 1],
        'freight'       => ['base' => 20, 'perKg' => 1.2, 'days' => 5],
        'international'  => ['base' => 12, 'perKg' => 4.0, 'days' => 5],
    ];

    private const FUEL_SURCHARGE = 0.16;   // 16%
    private const DIM_DIVISOR = 5000;      // cm^3 per kg
    private const MIN_CHARGE = 8.0;
    private const INTL_ZONE = 1.35;        // cross-border multiplier

    /**
     * @return array{cost: float, days: int, billableWeight: float}
     */
    public function quote(
        string $serviceType,
        float $weight,
        ?string $dimensions,
        string $originCountry,
        string $destinationCountry,
    ): array {
        $rate = self::RATES[$serviceType] ?? self::RATES['standard'];

        $dimWeight = $this->dimensionalWeight($dimensions);
        $billable = max($weight, $dimWeight, 1.0);

        $international = strcasecmp(trim($originCountry), trim($destinationCountry)) !== 0;
        $zone = $international ? self::INTL_ZONE : 1.0;

        $cost = ($rate['base'] + $billable * $rate['perKg']) * $zone * (1 + self::FUEL_SURCHARGE);
        $cost = max($cost, self::MIN_CHARGE);

        $days = $rate['days'] + ($international ? 3 : 0);

        return [
            'cost' => round($cost, 2),
            'days' => $days,
            'billableWeight' => round($billable, 2),
        ];
    }

    /** Parse "LxWxH" (cm) into a volumetric weight in kg. */
    private function dimensionalWeight(?string $dimensions): float
    {
        if (! $dimensions) {
            return 0.0;
        }
        if (! preg_match_all('/\d+(?:\.\d+)?/', $dimensions, $m) || count($m[0]) < 3) {
            return 0.0;
        }
        [$l, $w, $h] = array_map('floatval', array_slice($m[0], 0, 3));

        return ($l * $w * $h) / self::DIM_DIVISOR;
    }
}
