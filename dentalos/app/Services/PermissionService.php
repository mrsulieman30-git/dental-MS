<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Permission;

class PermissionService
{
    public static function getRolePermissions(string $role): array
    {
        return match ($role) {
            'super_admin' => array_column(Permission::cases(), 'value'),
            
            'admin' => array_column(Permission::cases(), 'value'), // Can refine later
            
            'doctor' => [
                Permission::PATIENTS_READ->value, Permission::PATIENTS_WRITE->value,
                Permission::CHART_READ->value, Permission::CHART_WRITE->value,
                Permission::PERIO_READ->value, Permission::PERIO_WRITE->value,
                Permission::NOTES_READ->value, Permission::NOTES_WRITE->value,
                Permission::NOTES_LOCK->value, Permission::VITAL_SIGNS_READ->value,
                Permission::PRESCRIPTIONS_READ->value, Permission::PRESCRIPTIONS_CREATE->value,
                Permission::TREATMENT_PLANS_READ->value, Permission::TREATMENT_PLANS_WRITE->value,
                Permission::IMAGING_READ->value, Permission::IMAGING_UPLOAD->value,
                Permission::SCHEDULE_READ->value, Permission::SCHEDULE_WRITE->value,
                Permission::RECALL_READ->value,
            ],
            
            'hygienist' => [
                Permission::PATIENTS_READ->value,
                Permission::CHART_READ->value,
                Permission::PERIO_READ->value, Permission::PERIO_WRITE->value,
                Permission::NOTES_READ->value, Permission::NOTES_WRITE->value,
                Permission::VITAL_SIGNS_READ->value, Permission::VITAL_SIGNS_WRITE->value,
                Permission::IMAGING_READ->value, Permission::IMAGING_UPLOAD->value,
                Permission::SCHEDULE_READ->value,
                Permission::RECALL_READ->value, Permission::RECALL_WRITE->value,
            ],
            
            'front_desk' => [
                Permission::PATIENTS_READ->value, Permission::PATIENTS_WRITE->value,
                Permission::SCHEDULE_READ->value, Permission::SCHEDULE_WRITE->value,
                Permission::WAITLIST_MANAGE->value,
                Permission::FORMS_READ->value, Permission::FORMS_WRITE->value,
                Permission::COMMUNICATIONS_READ->value, Permission::COMMUNICATIONS_SEND->value,
                Permission::BILLING_READ->value,
            ],
            
            'billing' => [
                Permission::PATIENTS_READ->value,
                Permission::BILLING_READ->value, Permission::BILLING_POST_PAYMENT->value,
                Permission::BILLING_ADJUSTMENTS->value, Permission::BILLING_WRITEOFF->value,
                Permission::CLAIMS_READ->value, Permission::CLAIMS_CREATE->value,
                Permission::CLAIMS_SUBMIT->value, Permission::ERA_POST->value,
                Permission::REPORTS_VIEW->value,
            ],
            
            'read_only' => [
                Permission::PATIENTS_READ->value,
                Permission::CHART_READ->value,
                Permission::SCHEDULE_READ->value,
                Permission::BILLING_READ->value,
            ],
            
            default => [],
        };
    }
}
