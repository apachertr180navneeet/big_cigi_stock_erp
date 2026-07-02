<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Page::create([
            'key' => "faqs",
            'name' => "Faqs",
            'value' => '',
        ]);

        Page::create([
            'key' => "about-app",
            'name' => "About App",
            'value' => '',
        ]);

        Page::create([
            'key' => "term-condition",
            'name' => "Term & Condition",
            'value' => '',
        ]);

        Page::create([
            'key' => "privacy-policy",
            'name' => "Privacy Policy",
            'value' => '',
        ]);
    }
}
