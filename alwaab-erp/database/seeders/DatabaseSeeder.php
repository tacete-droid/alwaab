<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            DemoDataSeeder::class,
            FlowGuardProductSeeder::class,
            QuotationDemoSeeder::class,
            ChatDemoSeeder::class,
            HrDemoSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
