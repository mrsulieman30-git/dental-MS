<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder requires a tenant to be present, usually called from DemoDataSeeder
    }

    public static function seedForTenant($tenantId, $userId)
    {
        // Appointment Types
        $apptTypes = [
            ['name' => 'Comprehensive Exam', 'color' => '#0ea5e9', 'duration' => 60, 'is_new' => true],
            ['name' => 'Periodic Exam', 'color' => '#10b981', 'duration' => 30, 'is_new' => false],
            ['name' => 'Emergency Exam', 'color' => '#f43f5e', 'duration' => 30, 'is_new' => false],
            ['name' => 'Prophy Adult', 'color' => '#6366f1', 'duration' => 60, 'is_new' => false],
            ['name' => 'Prophy Child', 'color' => '#8b5cf6', 'duration' => 45, 'is_new' => false],
            ['name' => 'SRP (Scaling/Root Planing)', 'color' => '#f59e0b', 'duration' => 90, 'is_new' => false],
            ['name' => 'Filling / Composite', 'color' => '#ec4899', 'duration' => 60, 'is_new' => false],
            ['name' => 'Crown / Bridge', 'color' => '#14b8a6', 'duration' => 90, 'is_new' => false],
            ['name' => 'Root Canal', 'color' => '#64748b', 'duration' => 120, 'is_new' => false],
            ['name' => 'Extraction', 'color' => '#ef4444', 'duration' => 45, 'is_new' => false],
        ];

        foreach ($apptTypes as $type) {
            DB::table('appointment_types')->insert([
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => $type['name'],
                'color' => $type['color'],
                'default_duration_minutes' => $type['duration'],
                'is_new_patient' => $type['is_new'],
                'created_at' => now(),
            ]);
        }

        // Note Templates
        $noteTemplates = [
            ['name' => 'SOAP: Comprehensive Exam', 'type' => 'soap', 'content' => "S: Patient here for comprehensive exam.\nO: Health history reviewed. Extraoral/Intraoral exam performed.\nA: Treatment plan discussed.\nP: Schedule next visit."],
            ['name' => 'SOAP: Composite Restoration', 'type' => 'soap', 'content' => "S: Patient here for restoration on tooth #.\nO: Local anesthetic administered. Decay removed. Composite placed.\nA: Good prognosis.\nP: Monitor at next visit."],
        ];

        foreach ($noteTemplates as $template) {
            DB::table('note_templates')->insert([
                'id' => Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => $template['name'],
                'note_type' => $template['type'],
                'template_content' => $template['content'],
                'created_by' => $userId,
                'is_active' => true,
                'created_at' => now(),
            ]);
        }
    }
}
