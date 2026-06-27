<?php

namespace App\Support;

class Status
{
    public static function label(string $status): string
    {
        return ucwords(str_replace('_', ' ', $status));
    }

    /** Tailwind classes for a status badge. */
    public static function badge(string $status): string
    {
        return match ($status) {
            'delivered' => 'bg-emerald-100 text-emerald-800',
            'in_transit', 'out_for_delivery' => 'bg-blue-100 text-blue-800',
            'processing', 'pending' => 'bg-amber-100 text-amber-800',
            'on_hold' => 'bg-orange-100 text-orange-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'accepted', 'paid', 'sent', 'active' => 'bg-emerald-100 text-emerald-800',
            'draft', 'expired' => 'bg-gray-100 text-gray-700',
            'overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
