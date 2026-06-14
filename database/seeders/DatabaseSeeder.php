<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin account for local dev.
        User::firstOrCreate(
            ['email' => 'admin@noor.test'],
            ['name' => 'Marketplace Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        // Starter addon categories.
        $categories = [
            ['Marketing & Growth', 'fa-bullhorn'],
            ['Analytics & Reports', 'fa-chart-line'],
            ['Integrations', 'fa-plug'],
            ['Engagement', 'fa-bolt'],
            ['UI & Widgets', 'fa-wand-magic-sparkles'],
            ['Payments & Billing', 'fa-credit-card'],
            ['Support & Ops', 'fa-headset'],
        ];

        foreach ($categories as $i => [$name, $icon]) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
