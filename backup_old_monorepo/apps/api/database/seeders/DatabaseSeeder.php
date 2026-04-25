<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CdtCodeSeeder::class,
            Icd10CodeSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
