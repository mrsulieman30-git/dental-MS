<?php

namespace App\Listeners;

use App\Events\PatientViewed;
use App\Services\AuditService;

class AuditPatientView
{
    public function __construct(protected AuditService $audit) {}

    public function handle(PatientViewed $event): void
    {
        $this->audit->log(
            action: 'viewed',
            resourceType: 'Patient',
            resourceId: $event->patient->id,
            metadata: ['patient_name' => $event->patient->full_name]
        );
    }
}
