<?php

declare(strict_types=1);

namespace App\Http\Resources\SmartOlt;

use App\DTOs\SmartOlt\OltData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OltResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof OltData) {
            return $this->resource->toArray();
        }

        return [
            'id' => (string) $this->external_id,
            'name' => $this->name,
            'olt_hardware_version' => $this->hardware_version,
            'ip' => $this->ip,
            'telnet_port' => $this->telnet_port ? (string) $this->telnet_port : null,
            'snmp_port' => $this->snmp_port ? (string) $this->snmp_port : null,
            'status' => $this->status,
            'metrics' => $this->metrics,
        ];
    }
}
