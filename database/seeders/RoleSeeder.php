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
                    // Laporan Pengeluaran
                    'report.expense.view',
                    // Laporan Keuangan
                    'report.income.view',
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
                    'expense.view',
                    'income.view',
                    'report.order.view',
                    'report.order.export',
                    'report.expense.view',
                    'report.expense.export',
                    'report.income.view',
                    'report.income.export',
                ],
            ],
            [
                'name' => 'Courier',
                'guard_name' => 'web',
                'permissions' => [
                    'dashboard.view',
                    'courier-task.view',
                    'courier-task.update-status',
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
