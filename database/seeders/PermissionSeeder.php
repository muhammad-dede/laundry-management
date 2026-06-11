<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            // User
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            // Service
            'service.view',
            'service.create',
            'service.update',
            'service.delete',
            // Customer
            'customer.view',
            'customer.create',
            'customer.update',
            'customer.delete',
            // Order
            'order.view',
            'order.create',
            'order.update',
            'order.delete',
            'order.detail',
            'order.cancel',
            // Order Status
            'order-status.view',
            'order-status.update',
            'order-status.history',
            // Payment
            'payment.view',
            'payment.create',
            'payment.update',
            'payment.delete',
            'payment.refund',
            // Antar Jemput (Pickup)
            'pickup.view',
            'pickup.create',
            'pickup.update',
            'pickup.delete',
            'pickup.assign-courier',
            // Antar Jemput (Delivery)
            'delivery.view',
            'delivery.create',
            'delivery.update',
            'delivery.delete',
            'delivery.assign-courier',
            // Tugas Kurir
            'courier-task.view',
            'courier-task.update-status',
            // Pengeluaran
            'expense.view',
            'expense.create',
            'expense.update',
            'expense.delete',
            // Pemasukan Non Transaksi
            'income.view',
            'income.create',
            'income.update',
            'income.delete',
            // Notifikasi WhatsApp
            'notification.view',
            'notification.send',
            'notification.resend',
            // Laporan Transaksi
            'report.transaction.view',
            'report.transaction.export',
            // Laporan Pembayaran
            'report.payment.view',
            'report.payment.export',
            // Laporan Pengeluaran
            'report.expense.view',
            'report.expense.export',
            // Laporan Keuangan
            'report.finance.view',
            'report.finance.export',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
