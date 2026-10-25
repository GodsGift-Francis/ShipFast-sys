<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Services\Notifications\NotificationService;
use App\Support\Country;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentStatusService
{
    public const STATUSES = ['pending', 'processing', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'on_hold'];

    private const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'], 'processing' => ['in_transit', 'on_hold', 'cancelled'],
        'in_transit' => ['out_for_delivery', 'on_hold'], 'out_for_delivery' => ['delivered', 'on_hold'],
        'on_hold' => ['processing', 'in_transit', 'out_for_delivery'], 'delivered' => [], 'cancelled' => []];

    public function __construct(private NotificationService $notifications) {}

    public static function allowed(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    public static function trackingNumber(): string
    {
        do {
            $code = 'SHF-'.strtoupper(Str::random(6)).'-'.strtoupper(Str::random(6));
        } while (Shipment::where('tracking_number', $code)->exists());

        return $code;
    }

    public function apply(Shipment $shipment, string $status, ?string $location = null, ?string $description = null, bool $notify = true, ?string $deliveredTo = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $status, $location, $description, $notify, $deliveredTo) {
            $locked = Shipment::lockForUpdate()->findOrFail($shipment->id);
            $from = $locked->status;
            if (! in_array($status, self::STATUSES, true) || ($from !== $status && ! in_array($status, self::allowed($from), true)) || (in_array($from, ['delivered', 'cancelled'], true))) {
                throw ValidationException::withMessages(['status' => 'This status change is not allowed from '.str_replace('_', ' ', $from).'.']);
            }
            if ($status === 'on_hold' && ! trim($description ?? '')) {
                throw ValidationException::withMessages(['description' => 'Add a reason for the delivery exception so your team can resolve it.']);
            }
            if ($status === 'delivered' && $locked->signature_required && ! trim($deliveredTo ?? '')) {
                throw ValidationException::withMessages(['delivered_to' => 'Enter the name of the person who received this signature-required shipment.']);
            }
            if ($from === 'pending' && $status === 'processing') {
                $required = ['sender_name', 'recipient_name', 'origin_address', 'destination_address', 'contents_description', 'weight'];
                if (Country::international($locked->origin_country, $locked->destination_country)) {
                    $required = [...$required, 'declared_value', 'country_of_manufacture'];
                }
                foreach ($required as $field) {
                    if (! $locked->$field) {
                        throw ValidationException::withMessages(['status' => 'Complete the booking details before collection: '.str_replace('_', ' ', $field).'.']);
                    }
                }
            }
            TrackingEvent::create(['shipment_id' => $locked->id, 'status' => $status, 'location' => $location ?: ($status === 'pending' ? "{$locked->origin_city}, {$locked->origin_country}" : 'Location not recorded'), 'description' => $description ?: 'Status updated to '.str_replace('_', ' ', $status), 'timestamp' => Carbon::now()]);
            $locked->status = $status;
            if ($status === 'delivered') {
                $locked->actual_delivery = Carbon::now();
                $locked->delivered_to = $deliveredTo;
            }
            $locked->save();
            if ($from !== $status && $notify) {
                DB::afterCommit(fn () => $this->notifications->notifyStatusChange($locked->fresh(), $from, $status));
            }

            return $locked->fresh();
        });
    }
}
