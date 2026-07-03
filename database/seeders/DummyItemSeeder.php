<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ItemMaster::create([
            'name' => 'ROYAL SPL FILTER 10BE-200S-4',
            'hsn' => '24022030',
            'brand_code' => '3076',
            'description' => '10BE-200s-4m',
            'status' => 1,
        ]);

        \App\Models\ItemMaster::create([
            'name' => 'GOLDFLAKE FK SOCIAL 2-POD',
            'hsn' => '24022090',
            'brand_code' => '3441',
            'description' => '2-POD 2',
            'status' => 1,
        ]);
    }
}
