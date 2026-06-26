<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type', 'origin_city', 'origin_country',
        'destination_city', 'destination_country', 'weight', 'dimensions',
        'contact_name', 'contact_email', 'estimated_cost', 'estimated_days', 'status',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'estimated_cost' => 'decimal:2',
            'estimated_days' => 'integer',
        ];
    }
}
