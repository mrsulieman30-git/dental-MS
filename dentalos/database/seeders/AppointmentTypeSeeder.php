<?php
namespace Database\Seeders;
use App\Models\Tenant;
use App\Models\AppointmentType;
use Illuminate\Database\Seeder;

class AppointmentTypeSeeder extends Seeder {
    public function run(): void {
        $tenant = Tenant::where('slug', 'demo')->first();
        $types = [
            ['name' => 'New Patient Exam', 'default_duration_minutes' => 60, 'color' => '#3498DB', 'is_new_patient' => true],
            ['name' => 'Recall Exam', 'default_duration_minutes' => 45, 'color' => '#2ECC71', 'is_new_patient' => false],
            ['name' => 'Prophylaxis', 'default_duration_minutes' => 60, 'color' => '#27AE60', 'is_new_patient' => false],
            ['name' => 'Periodontal Maintenance', 'default_duration_minutes' => 60, 'color' => '#16A085', 'is_new_patient' => false],
            ['name' => 'Emergency', 'default_duration_minutes' => 30, 'color' => '#E74C3C', 'is_new_patient' => false],
            ['name' => 'Crown Preparation', 'default_duration_minutes' => 90, 'color' => '#F39C12', 'is_new_patient' => false],
            ['name' => 'Crown Seat', 'default_duration_minutes' => 60, 'color' => '#F1C40F', 'is_new_patient' => false],
            ['name' => 'Composite Filling', 'default_duration_minutes' => 60, 'color' => '#E67E22', 'is_new_patient' => false],
            ['name' => 'Root Canal', 'default_duration_minutes' => 90, 'color' => '#9B59B6', 'is_new_patient' => false],
            ['name' => 'Extraction Simple', 'default_duration_minutes' => 45, 'color' => '#D35400', 'is_new_patient' => false],
            ['name' => 'Extraction Surgical', 'default_duration_minutes' => 90, 'color' => '#C0392B', 'is_new_patient' => false],
            ['name' => 'Orthodontic Check', 'default_duration_minutes' => 30, 'color' => '#8E44AD', 'is_new_patient' => false],
        ];
        foreach ($types as $type) {
            AppointmentType::create($type + ['tenant_id' => $tenant->id, 'is_active' => true]);
        }
    }
}
