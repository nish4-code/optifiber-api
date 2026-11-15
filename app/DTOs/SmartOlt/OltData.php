<?php

declare(strict_types=1);

namespace App\DTOs\SmartOlt;

final readonly class OltData
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $hardwareVersion,
        public ?string $ip,
        public ?int $telnetPort,
        public ?int $snmpPort,
        public string $status,
        public ?array $metrics = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            name: (string) ($data['name'] ?? 'OLT'),
            hardwareVersion: $data['olt_hardware_version'] ?? $data['hardware_version'] ?? null,
            ip: $data['ip'] ?? null,
            telnetPort: isset($data['telnet_port']) ? (int) $data['telnet_port'] : null,
            snmpPort: isset($data['snmp_port']) ? (int) $data['snmp_port'] : null,
            status: (string) ($data['status'] ?? 'online'),
            metrics: $data['metrics'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'olt_hardware_version' => $this->hardwareVersion,
            'ip' => $this->ip,
            'telnet_port' => $this->telnetPort,
            'snmp_port' => $this->snmpPort,
            'status' => $this->status,
            'metrics' => $this->metrics,
        ];
    }
}

