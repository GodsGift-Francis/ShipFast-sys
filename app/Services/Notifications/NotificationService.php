<?php

namespace App\Services\Notifications;

use App\Mail\ShipmentStatusMail;
use App\Models\NotificationDelivery;
use App\Models\Shipment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /** Human-friendly copy per status. */
    private const COPY = [
        'pending'          => ['title' => 'Shipment booked',           'body' => 'has been booked and is awaiting pickup.'],
        'processing'       => ['title' => 'Shipment processing',       'body' => 'is being processed at our facility.'],
        'in_transit'       => ['title' => 'Your shipment is moving',   'body' => 'is now in transit.'],
        'out_for_delivery' => ['title' => 'Out for delivery',          'body' => 'is out for delivery today.'],
        'delivered'        => ['title' => 'Delivered',                 'body' => 'has been delivered. Thank you for shipping with us.'],
        'cancelled'        => ['title' => 'Shipment cancelled',        'body' => 'has been cancelled.'],
        'on_hold'          => ['title' => 'Shipment on hold',          'body' => 'is currently on hold.'],
    ];

    public function notifyStatusChange(Shipment $shipment, ?string $from, string $to): void
    {
        $copy = self::COPY[$to] ?? ['title' => 'Shipment update', 'body' => "status changed to {$to}."];
        $subject = "{$copy['title']} — {$shipment->tracking_number}";
        $body = "Your shipment {$shipment->tracking_number} {$copy['body']}";

        $this->email($shipment, $shipment->recipient_email, $subject, $body, $from, $to);
        $this->sms($shipment, $shipment->recipient_phone, $body, $from, $to);
    }

    private function email(Shipment $shipment, ?string $to, string $subject, string $body, ?string $from, string $statusTo): void
    {
        if (! $to) {
            $this->record($shipment, 'email', null, $subject, $body, $from, $statusTo, 'skipped', 'No recipient email');
            return;
        }
        try {
            Mail::to($to)->send(new ShipmentStatusMail($shipment, $subject, $body));
            $this->record($shipment, 'email', $to, $subject, $body, $from, $statusTo, 'sent');
        } catch (\Throwable $e) {
            Log::error('Notification email failed', ['error' => $e->getMessage()]);
            $this->record($shipment, 'email', $to, $subject, $body, $from, $statusTo, 'failed', $e->getMessage());
        }
    }

    private function sms(Shipment $shipment, ?string $to, string $body, ?string $from, string $statusTo): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $sender = config('services.twilio.from');

        if (! $to) {
            $this->record($shipment, 'sms', null, null, $body, $from, $statusTo, 'skipped', 'No recipient phone');
            return;
        }
        if (! $sid || ! $token || ! $sender) {
            // No provider configured — log instead of sending (mirrors prior behaviour).
            Log::info('SMS (no provider configured)', ['to' => $to, 'body' => $body]);
            $this->record($shipment, 'sms', $to, null, $body, $from, $statusTo, 'skipped', 'SMS provider not configured');
            return;
        }
        try {
            $resp = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $sender,
                    'To' => $to,
                    'Body' => $body,
                ]);
            $status = $resp->successful() ? 'sent' : 'failed';
            $this->record($shipment, 'sms', $to, null, $body, $from, $statusTo, $status, $resp->successful() ? null : $resp->body());
        } catch (\Throwable $e) {
            $this->record($shipment, 'sms', $to, null, $body, $from, $statusTo, 'failed', $e->getMessage());
        }
    }

    private function record(Shipment $shipment, string $channel, ?string $recipient, ?string $subject, ?string $body, ?string $from, ?string $to, string $status, ?string $error = null): void
    {
        NotificationDelivery::create([
            'shipment_id' => $shipment->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'status_from' => $from,
            'status_to' => $to,
            'status' => $status,
            'error' => $error,
        ]);
    }
}
