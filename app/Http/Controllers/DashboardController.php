<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Shipment;
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
            'revenue' => (float) Shipment::sum('shipping_cost'),
            'monthly_revenue' => (float) Shipment::where('created_at', '>=', Carbon::now()->startOfMonth())->sum('shipping_cost'),
            'customers' => Customer::count(),
        ];

        $chartLabels = [];
        $chartData = [];
        foreach (\App\Services\ShipmentStatusService::STATUSES as $s) {
            $chartLabels[] = ucwords(str_replace('_', ' ', $s));
            $chartData[] = (int) ($statusCounts[$s] ?? 0);
        }

        $recent = Shipment::latest()->limit(6)->get();

        return view('portal.dashboard', compact('stats', 'chartLabels', 'chartData', 'recent'));
    }
}
