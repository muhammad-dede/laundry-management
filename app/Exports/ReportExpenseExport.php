<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExpenseExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $startDate,
        protected string $endDate
    ) {}

    public function collection()
    {
        return Expense::query()
            ->with('createdBy')
            ->whereDate('expense_date', '>=', $this->startDate)
            ->whereDate('expense_date', '<=', $this->endDate)
            ->get()
            ->map(function ($item) {
                return [
                    'expense_date' => $item->expense_date,
                    'category' => $item->category,
                    'description' => $item->description,
                    'amount' => $item->amount,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Deskripsi',
            'Nominal',
        ];
    }
}
