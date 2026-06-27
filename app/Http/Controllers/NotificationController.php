<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\NotificationDelivery;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::with('shipment')->latest()->limit(50)->get();
        $deliveries = NotificationDelivery::with('shipment')->latest()->paginate(20);

        return view('portal.notifications.index', compact('notifications', 'deliveries'));
    }
}
