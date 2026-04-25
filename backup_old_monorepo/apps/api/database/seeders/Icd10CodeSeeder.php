<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Icd10CodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['code' => 'K02.3', 'description' => 'Arrested dental caries'],
            ['code' => 'K02.51', 'description' => 'Dental caries on pit and fissure surface limited to enamel'],
            ['code' => 'K02.61', 'description' => 'Dental caries on smooth surface limited to enamel'],
            ['code' => 'K03.0', 'description' => 'Excessive attrition of teeth'],
            ['code' => 'K04.01', 'description' => 'Reversible pulpitis'],
            ['code' => 'K04.02', 'description' => 'Irreversible pulpitis'],
            ['code' => 'K05.00', 'description' => 'Acute gingivitis, plaque induced'],
            ['code' => 'K05.10', 'description' => 'Chronic gingivitis, plaque induced'],
            ['code' => 'K05.211', 'description' => 'Aggressive periodontitis, localized, maxillary'],
            ['code' => 'K05.311', 'description' => 'Chronic periodontitis, localized, maxillary'],
        ];

        foreach ($codes as $code) {
            DB::table('medical_conditions')->insert([
                'id' => Str::uuid(),
                'medical_history_id' => Str::uuid(), // This is just a lookup seeder for conditions if needed, or I can just define them as constants
                'condition_name' => $code['description'],
                'icd10_code' => $code['code'],
                'is_active' => true,
                'created_at' => now(),
            ]);
        }
    }
}
