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

        $revenueChart = collect(range(6, 0))
            ->map(function ($day) {
                $date = Carbon::today()->subDays($day);

                return [
                    'date' => $date->format('d M'),
                    'total' => Order::query()
                        ->whereDate('order_date', $date)
                        ->sum('grand_total'),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'today_orders' => Order::whereDate('order_date', $today)->count(),
                'today_revenue' => Order::whereDate('order_date', $today)->sum('grand_total'),
                'processing_orders' => Order::whereIn('order_status', ['QUEUED', 'PROCESS',])->count(),
                'completed_today' => Order::whereDate('updated_at', $today)->where('order_status', 'COMPLETED')->count(),
            ],
            'latest_orders' => Order::with('customer')->latest()->take(10)->get(),
            'chart' => [
                'categories' => $revenueChart->pluck('date'),
                'series' => $revenueChart->pluck('total'),
            ],
        ]);
    }
}
