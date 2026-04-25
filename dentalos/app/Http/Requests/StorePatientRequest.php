<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules() {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'status' => 'nullable|string',
            'primary_location_id' => 'nullable|exists:locations,id',
            'primary_provider_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ];
    }
}
