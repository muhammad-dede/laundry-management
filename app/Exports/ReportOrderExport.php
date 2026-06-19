<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportOrderExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected string $startDate,
        protected string $endDate
    ) {}

    public function collection()
    {
        return Order::query()
            ->with('customer')
            ->whereDate('order_date', '>=', $this->startDate)
            ->whereDate('order_date', '<=', $this->endDate)
            ->get()
            ->map(function ($item) {
                return [
                    'invoice' => $item->invoice_number,
                    'customer' => $item->customer?->name,
                    'order_date' => $item->order_date,
                    'estimated_finish_date' => $item->estimated_finish_date,
                    'total' => $item->grand_total,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Invoice',
            'Pelanggan',
            'Tanggal Pesanan',
            'Estimasi Selesai',
            'Total',
        ];
    }
}
