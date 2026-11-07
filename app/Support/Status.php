<?php

namespace App\Support;

class Status
{
    public static function nextStep(string $status): ?array
    {
        return match ($status) {
            'pending' => [
                'title' => 'Collection scan',
                'description' => 'A collection scan confirms the parcel has entered the shipping network. The arrival estimate may update after that scan.',
            ],
            'processing' => [
                'title' => 'Transit scan',
                'description' => 'The next facility scan will show where the parcel is as it moves toward its destination.',
            ],
            'in_transit' => [
                'title' => 'Out for delivery',
                'description' => 'A local delivery update will appear when the parcel is assigned to its final delivery route.',
            ],
            'out_for_delivery' => [
                'title' => 'Delivery confirmation',
                'description' => 'A delivery confirmation will appear once the courier records the completed handoff.',
            ],
            'on_hold' => [
                'title' => 'Exception review',
                'description' => 'The shipping team is reviewing this exception. If anything is needed from you, your operator will share the next steps.',
            ],
            'delivered' => [
                'title' => 'Journey complete',
                'description' => 'Delivery has been recorded. Keep this tracking number for your records.',
            ],
            'cancelled' => [
                'title' => 'Booking closed',
                'description' => 'This shipment was cancelled, so no further tracking updates are expected.',
            ],
            default => null,
        };
    }

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
