<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            TemplateSeeder::class,
            SettingSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
