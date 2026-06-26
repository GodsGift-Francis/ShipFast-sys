<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRoute extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'origin_city', 'origin_country', 'destination_city', 'destination_country',
        'service_type', 'transit_days', 'base_price', 'currency', 'active',
    ];

    protected function casts(): array
    {
        return [
            'transit_days' => 'integer',
            'base_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }
}
