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
            'purchase_uom' => 'M_S',
            'sales_uom' => 'PAC',
            'conversion_factor' => 100,
            'purchase_rate' => 3366.89,
            'sales_rate' => 25.29,
            'mrp' => 55.00,
            'cgst_percentage' => 14,
            'sgst_percentage' => 14,
            'description' => '10BE-200s-4m',
            'status' => 1,
        ]);

        \App\Models\ItemMaster::create([
            'name' => 'GOLDFLAKE FK SOCIAL 2-POD',
            'hsn' => '24022090',
            'brand_code' => '3441',
            'purchase_uom' => 'M_S',
            'sales_uom' => 'PAC',
            'conversion_factor' => 50,
            'purchase_rate' => 6982.07,
            'sales_rate' => 187.10,
            'mrp' => 300.00,
            'cgst_percentage' => 14,
            'sgst_percentage' => 14,
            'description' => '2-POD 2',
            'status' => 1,
        ]);
    }
}
