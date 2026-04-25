<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CdtCodeSeeder::class,
            TenantSeeder::class,
            LocationSeeder::class,
            UserSeeder::class,
            AppointmentTypeSeeder::class,
            AdjustmentTypeSeeder::class,
        ]);
    }
}
