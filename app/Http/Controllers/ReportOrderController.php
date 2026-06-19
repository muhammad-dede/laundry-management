<?php

namespace App\Http\Controllers;

use App\Exports\ReportOrderExport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportOrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);

        $start_date = $filters['start_date'] ?? Carbon::today()->toDateString();
        $end_date = $filters['end_date'] ?? Carbon::today()->toDateString();

        $data = Order::query()
            ->with(['customer', 'createdBy'])
            ->whereDate('order_date', '>=', $start_date)
            ->whereDate('order_date', '<=', $end_date)
            ->orderBy('order_date', 'desc')
            ->get();

        return Inertia::render('report-order/Index', [
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
            ],
            'data' => $data,
            'summary' => [
                'total_transaction' => $data->count(),
                'total_amount' => $data->sum('grand_total'),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return Excel::download(
            new ReportOrderExport($startDate, $endDate),
            'laporan-pesanan.xlsx'
        );
    }
}
