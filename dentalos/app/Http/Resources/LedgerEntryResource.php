<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LedgerEntryResource extends JsonResource
{
    public function toArray($request) {
        return [
            'id' => $this->id,
            'entry_date' => $this->entry_date->toDateString(),
            'entry_type' => $this->entry_type,
            'amount' => (float)$this->amount,
            'description' => $this->description,
            'cdt_code' => $this->cdtCode ? $this->cdtCode->code : null,
            'tooth_number' => $this->tooth_number,
            'surfaces' => $this->surfaces,
            'status' => $this->status,
        ];
    }
}
