<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number', 'status', 'service_type', 'customer_id',
        'origin_address', 'origin_city', 'origin_country',
        'destination_address', 'destination_city', 'destination_country',
        'sender_name', 'sender_email', 'sender_phone',
        'recipient_name', 'recipient_email', 'recipient_phone',
        'weight', 'dimensions', 'shipping_cost',
        'estimated_delivery', 'actual_delivery', 'notes',
        'origin_postal_code', 'destination_postal_code', 'contents_description', 'declared_value', 'hs_code', 'country_of_manufacture', 'signature_required', 'delivered_to', 'rate_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'signature_required' => 'boolean', 'rate_breakdown' => 'array', 'declared_value' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'estimated_delivery' => 'datetime',
            'actual_delivery' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderByDesc('timestamp');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
