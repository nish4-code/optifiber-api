<?php

declare(strict_types=1);

namespace App\Http\Resources\SmartOlt;

use App\DTOs\SmartOlt\OnuSignalData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnuSignalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof OnuSignalData) {
            return $this->resource->toArray();
        }

        return [
            'onu_signal' => $this->signal_value ?? 'Unknown',
            'onu_signal_value' => $this->signal_value ?? '',
            'onu_signal_1310' => $this->signal_1310 ?? '',
            'onu_signal_1490' => $this->signal_1490 ?? '',
        ];
    }
}

