<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Admin',
            ],
        ];

        foreach ($users as $userData) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'email_verified_at' => $userData['verified_at'],
                ]
            );

            if (isset($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }
    }
}
