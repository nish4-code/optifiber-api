<?php

declare(strict_types=1);

namespace App\DTOs\SmartOlt;

final readonly class OnuData
{
    public function __construct(
        public string $uniqueExternalId,
        public string $sn,
        public ?string $name,
        public ?string $oltId,
        public ?string $oltName,
        public ?string $board,
        public ?string $port,
        public ?int $onuNumber,
        public ?string $onuTypeId,
        public ?string $onuTypeName,
        public ?string $zone,
        public ?string $odb,
        public string $status,
        public ?string $administrativeStatus,
        public ?string $catvStatus,
        public ?string $signalValue,
        public ?string $signal1310,
        public ?string $signal1490,
        public ?int $distance,
        public ?string $address,
        public ?float $latitude,
        public ?float $longitude,
        public ?int $vlan,
        public ?array $extra = null,
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            uniqueExternalId: (string) ($data['unique_external_id'] ?? $data['id'] ?? $data['sn'] ?? ''),
            sn: (string) ($data['sn'] ?? ''),
            name: $data['name'] ?? null,
            oltId: isset($data['olt_id']) ? (string) $data['olt_id'] : null,
            oltName: $data['olt_name'] ?? null,
            board: isset($data['board']) ? (string) $data['board'] : null,
            port: isset($data['port']) ? (string) $data['port'] : null,
            onuNumber: isset($data['onu']) ? (int) $data['onu'] : (isset($data['onu_number']) ? (int) $data['onu_number'] : null),
            onuTypeId: isset($data['onu_type_id']) ? (string) $data['onu_type_id'] : null,
            onuTypeName: $data['onu_type_name'] ?? null,
            zone: $data['zone'] ?? null,
            odb: $data['odb'] ?? null,
            status: (string) ($data['status'] ?? 'Online'),
            administrativeStatus: $data['administrative_status'] ?? 'Enabled',
            catvStatus: $data['catv_status'] ?? null,
            signalValue: $data['signal_value'] ?? null,
            signal1310: $data['signal_1310'] ?? null,
            signal1490: $data['signal_1490'] ?? null,
            distance: isset($data['distance']) ? (int) $data['distance'] : (isset($data['distance_m']) ? (int) $data['distance_m'] : null),
            address: $data['address'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            vlan: isset($data['vlan']) ? (int) $data['vlan'] : null,
            extra: $data['extra'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'unique_external_id' => $this->uniqueExternalId,
            'sn' => $this->sn,
            'name' => $this->name,
            'olt_id' => $this->oltId,
            'olt_name' => $this->oltName,
            'board' => $this->board,
            'port' => $this->port,
            'onu' => $this->onuNumber,
            'onu_type_id' => $this->onuTypeId,
            'onu_type_name' => $this->onuTypeName,
            'zone' => $this->zone,
            'odb' => $this->odb,
            'status' => $this->status,
            'administrative_status' => $this->administrativeStatus,
            'catv_status' => $this->catvStatus,
            'signal_value' => $this->signalValue,
            'signal_1310' => $this->signal1310,
            'signal_1490' => $this->signal1490,
            'distance' => $this->distance,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'vlan' => $this->vlan,
        ];
    }
}

