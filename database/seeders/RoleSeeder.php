<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'permissions' => [],
            ],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
                'permissions' => [
                    'dashboard.view',
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
                    // Notifikasi WhatsApp
                    'notification.view',
                    'notification.send',
                    'notification.resend',
                    // Laporan Pesanan
                    'report.transaction.view',
                    // Laporan Pembayaran
                    'report.payment.view',
                    // Laporan Pengeluaran
                    'report.expense.view',
                    // Laporan Keuangan
                    'report.finance.view',
                ],
            ],
            [
                'name' => 'Owner',
                'guard_name' => 'web',
                'permissions' => [
                    'dashboard.view',
                    'customer.view',
                    'service.view',
                    'order.view',
                    'order.detail',
                    'payment.view',
                    'expense.view',
                    'income.view',
                    'pickup.view',
                    'delivery.view',
                    'report.transaction.view',
                    'report.transaction.export',
                    'report.payment.view',
                    'report.payment.export',
                    'report.expense.view',
                    'report.expense.export',
                    'report.finance.view',
                    'report.finance.export',
                ],
            ],
            [
                'name' => 'Courier',
                'guard_name' => 'web',
                'permissions' => [
                    'dashboard.view',
                    'courier-task.view',
                    'courier-task.update-status',
                    'pickup.view',
                    'delivery.view',
                ],
            ],
        ];

        foreach ($roles as $role) {
            $roleModel = \Spatie\Permission\Models\Role::firstOrCreate(
                [
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name']
                ]
            );
            if ($role['name'] === 'Super Admin') {
                $permissions = \Spatie\Permission\Models\Permission::pluck('id')->toArray();
                $roleModel->syncPermissions($permissions);
            } else {
                if (isset($role['permissions'])) {
                    $roleModel->syncPermissions($role['permissions']);
                }
            }
        }
    }
}
