<?php
namespace Database\Seeders;
use App\Models\Tenant;
use App\Models\AdjustmentType;
use Illuminate\Database\Seeder;

class AdjustmentTypeSeeder extends Seeder {
    public function run(): void {
        $tenant = Tenant::where('slug', 'demo')->first();
        $adjustments = [
            ['name' => 'Insurance Write-off', 'code' => 'IWO', 'is_debit' => false, 'affects_production' => false, 'affects_collections' => false],
            ['name' => 'Professional Courtesy', 'code' => 'PC', 'is_debit' => false, 'affects_production' => true, 'affects_collections' => false],
            ['name' => 'Employee Discount', 'code' => 'ED', 'is_debit' => false, 'affects_production' => true, 'affects_collections' => false],
            ['name' => 'Collection Write-off', 'code' => 'CWO', 'is_debit' => false, 'affects_production' => false, 'affects_collections' => false],
            ['name' => 'Returned Check Fee', 'code' => 'NSF', 'is_debit' => true, 'affects_production' => false, 'affects_collections' => false],
            ['name' => 'Prepayment Discount', 'code' => 'PPD', 'is_debit' => false, 'affects_production' => true, 'affects_collections' => false],
        ];
        foreach ($adjustments as $adj) {
            AdjustmentType::create($adj + ['tenant_id' => $tenant->id, 'is_active' => true]);
        }
    }
}
