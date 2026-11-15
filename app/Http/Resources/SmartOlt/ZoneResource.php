<?php

declare(strict_types=1);

namespace App\Http\Resources\SmartOlt;

use App\DTOs\SmartOlt\ZoneData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ZoneData) {
            return $this->resource->toArray();
        }

        return [
            'id' => (string) $this->external_id,
            'name' => $this->name,
        ];
    }
}

