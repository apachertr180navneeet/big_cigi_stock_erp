<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SplashScreen;

class SplashScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SplashScreen::create([
            'type' => "onboarding",
            'heading' => "Find people who match with you",
            'content' => "",
            'image' => "assets\app\splash\onboarding_one.png",
        ]);

        SplashScreen::create([
            'type' => "onboarding",
            'heading' => "Easily message & call the people you like",
            'content' => "",
            'image' => "assets\app\splash\onboarding_two.png",
        ]);

        SplashScreen::create([
            'type' => "onboarding",
            'heading' => "Don`t wait anymore, find out your soul mate now",
            'content' => "",
            'image' => "assets\app\splash\onboarding_three.png",
        ]);

        SplashScreen::create([
            'type' => "welcome",
            'heading' => "Let's you in",
            'content' => "",
            'image' => "assets\app\splash\welcome.png",
        ]);
    }
}
