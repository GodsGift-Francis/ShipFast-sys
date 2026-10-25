<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Shipment;
use App\Services\ShipmentStatusService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $statusCounts = Shipment::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $stats = [
            'total' => Shipment::count(),
            'active' => Shipment::whereIn('status', ['processing', 'in_transit', 'out_for_delivery'])->count(),
            'delivered_today' => Shipment::where('status', 'delivered')
                ->whereDate('actual_delivery', Carbon::today())->count(),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'revenue' => (float) Shipment::where('status', '!=', 'cancelled')->sum('shipping_cost'),
            'attention_count' => Shipment::where(function ($q) {
                $q->where('status', 'on_hold')->orWhere(function ($q) {
                    $q->whereNotIn('status', ['delivered', 'cancelled'])->where('estimated_delivery', '<', now());
                });
            })->count(),
            'exceptions' => Shipment::where('status', 'on_hold')->count(),
            'overdue' => Shipment::whereNotIn('status', ['delivered', 'cancelled'])->where('estimated_delivery', '<', now())->count(),
            'monthly_revenue' => (float) Shipment::where('status', '!=', 'cancelled')->where('created_at', '>=', Carbon::now()->startOfMonth())->sum('shipping_cost'),
            'customers' => Customer::count(),
        ];

        $chartLabels = [];
        $chartData = [];
        foreach (ShipmentStatusService::STATUSES as $s) {
            $chartLabels[] = ucwords(str_replace('_', ' ', $s));
            $chartData[] = (int) ($statusCounts[$s] ?? 0);
        }

        $recent = Shipment::latest()->limit(8)->get();
        $attention = Shipment::where(function ($q) {
            $q->where('status', 'on_hold')->orWhere(function ($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled'])->where('estimated_delivery', '<', now());
            });
        })->orderBy('estimated_delivery')->limit(5)->get();

        return view('portal.dashboard', compact('stats', 'chartLabels', 'chartData', 'recent', 'attention'));
    }
}
