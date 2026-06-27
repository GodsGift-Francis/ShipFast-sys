<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ShipmentStatusService
{
    public const STATUSES = ['pending', 'processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'on_hold'];

    public function __construct(private NotificationService $notifications) {}

    /** Generate a unique tracking number like SHF-XXXX-XXXX. */
    public static function trackingNumber(): string
    {
        do {
            $code = 'SHF-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        } while (Shipment::where('tracking_number', $code)->exists());

        return $code;
    }

    /**
     * Single source of truth for status changes: records a tracking event,
     * updates the shipment, and fires notifications only on a genuine change.
     */
    public function apply(Shipment $shipment, string $status, ?string $location = null, ?string $description = null, bool $notify = true): Shipment
    {
        $isKnown = in_array($status, self::STATUSES, true);
        $from = $shipment->status;
        $changed = $isKnown && $status !== $from;

        // Always record a timeline event.
        TrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'location' => $location ?: "{$shipment->destination_city}, {$shipment->destination_country}",
            'description' => $description ?: ($isKnown ? 'Status updated to '.str_replace('_', ' ', $status) : $status),
            'timestamp' => Carbon::now(),
        ]);

        if ($isKnown) {
            $shipment->status = $status;
            if ($status === 'delivered' && ! $shipment->actual_delivery) {
                $shipment->actual_delivery = Carbon::now();
            }
            $shipment->save();
        }

        if ($changed && $notify) {
            $this->notifications->notifyStatusChange($shipment->fresh(), $from, $status);
        }

        return $shipment->fresh();
    }
}
