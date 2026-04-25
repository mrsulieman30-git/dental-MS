<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClinicalNoteResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            'note_type' => $this->note_type,
            'full_note_text' => $this->full_note_text,
            'subjective' => $this->subjective,
            'objective' => $this->objective,
            'assessment' => $this->assessment,
            'plan' => $this->plan,
            'is_locked' => $this->is_locked,
            'locked_at' => $this->locked_at,
            'is_signed' => $this->is_signed,
            'provider' => [
                'id' => $this->provider->id,
                'full_name' => $this->provider->full_name
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'amendments' => $this->is_amended ? $this->amendment_notes : null,
        ];
    }
}
