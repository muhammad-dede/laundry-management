<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        return Inertia::render('Dashboard', [
            'stats' => [
                'today_orders' => Order::query()->whereDate('order_date', '=', $today)->count(),
                'today_revenue' => Order::query()->whereDate('order_date', '=', $today)->sum('grand_total'),
                'processing_orders' => Order::query()->whereIn('order_status', ['QUEUED', 'PROCESS',])->count(),
                'completed_today' => Order::query()->whereDate('updated_at', $today)->where('order_status', 'COMPLETED')->count(),
            ],
            'latest_orders' => Order::with('customer')->latest()->take(10)->get(),
        ]);
    }
}
