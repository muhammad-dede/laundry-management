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
