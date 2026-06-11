<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Cuci Kering', 'unit_type' => 'KG', 'price' => 5000, 'estimated_days' => 2],
            ['name' => 'Cuci Setrika', 'unit_type' => 'KG', 'price' => 8000, 'estimated_days' => 3],
            ['name' => 'Setrika Saja', 'unit_type' => 'KG', 'price' => 3000, 'estimated_days' => 1],
            ['name' => 'Cuci Sepatu', 'unit_type' => 'PCS', 'price' => 15000, 'estimated_days' => 4],
            ['name' => 'Cuci Karpet', 'unit_type' => 'PCS', 'price' => 25000, 'estimated_days' => 4],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create($service);
        }
    }
}
