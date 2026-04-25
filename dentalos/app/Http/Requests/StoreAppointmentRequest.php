<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'patient_id' => 'required|exists:patients,id',
            'location_id' => 'required|exists:locations,id',
            'provider_id' => 'required|exists:users,id',
            'appointment_type_id' => 'required|exists:appointment_types,id',
            'operatory_id' => 'required|exists:operatories,id',
            'start_time' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:5',
            'notes' => 'nullable|string',
        ];
    }
}
