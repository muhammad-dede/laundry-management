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
            // Pengeluaran
            'expense.view',
            'expense.create',
            'expense.update',
            'expense.delete',
            // Pemasukan Non Pesanan
            'income.view',
            'income.create',
            'income.update',
            'income.delete',
            // Laporan Pesanan
            'report.order.view',
            'report.order.export',
            // Laporan Pengeluaran
            'report.expense.view',
            'report.expense.export',
            // Laporan Keuangan
            'report.income.view',
            'report.income.export',
            // Antar Jemput (Pickup)
            'pickup.view',
            'pickup.create',
            'pickup.update',
            'pickup.delete',
            // Antar Jemput (Delivery)
            'delivery.view',
            'delivery.create',
            'delivery.update',
            'delivery.delete',
            'delivery.assign-courier',
            // Tugas Kurir
            'courier-task.view',
            'courier-task.update-status',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
