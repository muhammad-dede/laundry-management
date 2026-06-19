<?php

namespace App\Http\Controllers;

use App\Exports\ReportExpenseExport;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportExpenseController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date']);

        $start_date = $filters['start_date'] ?? Carbon::today()->toDateString();
        $end_date = $filters['end_date'] ?? Carbon::today()->toDateString();

        $data = Expense::query()
            ->with(['createdBy'])
            ->whereDate('expense_date', '>=', $start_date)
            ->whereDate('expense_date', '<=', $end_date)
            ->orderBy('expense_date', 'desc')
            ->get();

        return Inertia::render('report-expense/Index', [
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
            new ReportExpenseExport($startDate, $endDate),
            'laporan-pengeluaran.xlsx'
        );
    }
}
