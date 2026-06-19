<?php

namespace App\Http\Controllers;

use App\Exports\ReportIncomeExport;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportIncomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);

        $start_date = $filters['start_date'] ?? Carbon::today()->toDateString();
        $end_date = $filters['end_date'] ?? Carbon::today()->toDateString();

        $data = Income::query()
            ->with(['createdBy'])
            ->whereDate('income_date', '>=', $start_date)
            ->whereDate('income_date', '<=', $end_date)
            ->orderBy('income_date', 'desc')
            ->get();

        return Inertia::render('report-income/Index', [
            'filters' => [
                'start_date' => $start_date,
                'end_date' => $end_date,
            ],
            'data' => $data,
            'summary' => [
                'count' => $data->count(),
                'total_amount' => $data->sum('amount'),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return Excel::download(
            new ReportIncomeExport($startDate, $endDate),
            'laporan-pemasukan.xlsx'
        );
    }
}
