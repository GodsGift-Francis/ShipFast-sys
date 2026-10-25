<?php

return [
    'currency' => 'USD', 'fuel_surcharge' => 0.16, 'dimensional_divisor' => 5000, 'minimum_charge' => 8,
    'international_multiplier' => 1.35, 'quote_valid_days' => 7,
    'services' => [
        'standard' => ['label' => 'Standard', 'base' => 4, 'per_kg' => 1.8, 'days' => 3, 'description' => 'Everyday parcels, thoughtfully priced.'],
        'express' => ['label' => 'Express', 'base' => 9, 'per_kg' => 3.5, 'days' => 2, 'description' => 'Priority handling for time-sensitive parcels.'],
        'freight' => ['label' => 'Freight', 'base' => 20, 'per_kg' => 1.2, 'days' => 5, 'description' => 'A practical choice for heavier cargo.'],
        'international' => ['label' => 'International', 'base' => 12, 'per_kg' => 4, 'days' => 5, 'description' => 'Cross-border parcels with customs details.'],
    ]];
