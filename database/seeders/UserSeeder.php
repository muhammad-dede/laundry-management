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
                'username' => 'superadmin',
                'email' => 'superadmin@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Admin',
            ],
            [
                'name' => 'Owner',
                'username' => 'owner',
                'email' => 'owner@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Owner',
            ],
            [
                'name' => 'Kurir',
                'username' => 'courier',
                'email' => 'courier@email.com',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role' => 'Courier',
            ],
        ];

        foreach ($users as $userData) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
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
