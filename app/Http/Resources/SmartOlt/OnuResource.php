<?php

declare(strict_types=1);

namespace App\Http\Resources\SmartOlt;

use App\DTOs\SmartOlt\OnuData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof OnuData) {
            return $this->resource->toArray();
        }

        return [
            'unique_external_id' => (string) $this->unique_external_id,
            'sn' => $this->sn,
            'name' => $this->name,
            'olt_id' => $this->olt ? (string) $this->olt->external_id : null,
            'olt_name' => $this->olt?->name,
            'board' => $this->board,
            'port' => $this->port,
            'onu' => $this->onu_number,
            'onu_type_id' => $this->onu_type_id,
            'onu_type_name' => $this->onu_type_name,
            'zone' => $this->zone?->name,
            'odb' => $this->odb?->name,
            'status' => $this->status,
            'administrative_status' => $this->administrative_status,
            'catv_status' => $this->catv_status,
            'signal_value' => $this->signal_value,
            'signal_1310' => $this->signal_1310,
            'signal_1490' => $this->signal_1490,
            'distance' => $this->distance_m,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'vlan' => $this->vlan,
        ];
    }
}

