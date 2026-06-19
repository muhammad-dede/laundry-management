<?php

namespace App\Exports;

use App\Models\Income;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportIncomeExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $startDate,
        protected string $endDate
    ) {}

    public function collection()
    {
        return Income::query()
            ->with('createdBy')
            ->whereDate('income_date', '>=', $this->startDate)
            ->whereDate('income_date', '<=', $this->endDate)
            ->get()
            ->map(function ($item) {
                return [
                    'income_date' => $item->income_date,
                    'description' => $item->description,
                    'amount' => $item->amount,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Deskripsi',
            'Nominal',
        ];
    }
}
