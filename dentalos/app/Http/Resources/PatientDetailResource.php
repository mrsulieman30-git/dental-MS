<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientDetailResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            'patient_number' => $this->patient_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'age' => $this->age,
            'gender' => $this->gender,
            'status' => $this->status,
            'alerts' => $this->alerts()->where('is_active', true)->get(),
            'upcoming_appointments' => $this->appointments()->where('start_time', '>', now())->orderBy('start_time')->limit(3)->get(),
            'insurance_summary' => $this->insurance()->where('is_primary', true)->first(),
            'current_balance' => $this->ledgerEntries()->sum('amount'),
            'medical_history_summary' => $this->medicalHistories()->where('is_current', true)->first(),
        ];
    }
}
