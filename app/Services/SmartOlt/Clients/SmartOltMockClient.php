<?php

declare(strict_types=1);

namespace App\Services\SmartOlt\Clients;

use App\Contracts\SmartOlt\SmartOltClientInterface;
use App\DTOs\SmartOlt\OltData;
use App\DTOs\SmartOlt\OnuData;
use App\DTOs\SmartOlt\OnuSignalData;
use App\DTOs\SmartOlt\ZoneData;
use Illuminate\Support\Collection;

class SmartOltMockClient implements SmartOltClientInterface
{
    public function request(string $method, string $endpoint, array $data = []): array
    {
        return [
            'status' => true,
            'response' => 'Mock operation executed',
            'endpoint' => $endpoint,
        ];
    }

    public function getOlts(): Collection
    {
        return collect([
            new OltData(
                id: '1',
                name: 'OLT-Principal-Chiclayo',
                hardwareVersion: 'Huawei-MA5800-X7',
                ip: '192.168.10.2',
                telnetPort: 2323,
                snmpPort: 2161,
                status: 'online',
                metrics: [
                    'cpu_usage' => 14,
                    'memory_usage' => 28,
                    'temperature' => 39.5,
                    'pon_ports_total' => 16,
                    'pon_ports_active' => 12,
                ]
            ),
            new OltData(
                id: '2',
                name: 'OLT-Secundaria-Lambayeque',
                hardwareVersion: 'ZTE-C320',
                ip: '192.168.20.2',
                telnetPort: 2323,
                snmpPort: 2161,
                status: 'online',
                metrics: [
                    'cpu_usage' => 9,
                    'memory_usage' => 21,
                    'temperature' => 37.0,
                    'pon_ports_total' => 8,
                    'pon_ports_active' => 6,
                ]
            ),
        ]);
    }

    public function getOltDetails(string $oltId): ?OltData
    {
        return $this->getOlts()->firstWhere('id', $oltId);
    }

    public function getOltUptimeAndTemperature(?string $oltId = null): array
    {
        return [
            'status' => true,
            'response' => [
                'uptime' => '45 days, 12:34:10',
                'environment_temperature' => '38.5 C',
                'fans_status' => 'Normal',
            ],
        ];
    }

    public function getOltCards(string $oltId): array
    {
        return [
            'status' => true,
            'response' => [
                ['slot' => 0, 'card_type' => 'MPLA', 'status' => 'Normal'],
                ['slot' => 1, 'card_type' => 'GPFD', 'status' => 'Normal', 'ports' => 16],
            ],
        ];
    }

    public function getOltPonPorts(string $oltId): array
    {
        return [
            'status' => true,
            'response' => [
                ['board' => '0/1', 'port' => 1, 'admin_status' => 'Up', 'oper_status' => 'Up', 'onus_count' => 32],
                ['board' => '0/1', 'port' => 2, 'admin_status' => 'Up', 'oper_status' => 'Up', 'onus_count' => 28],
            ],
        ];
    }

    public function getOltOutagePons(string $oltId): array
    {
        return [
            'status' => true,
            'response' => [],
        ];
    }

    public function getOltUplinkPorts(string $oltId): array
    {
        return [
            'status' => true,
            'response' => [
                ['port' => '0/9/0', 'speed' => '10GE', 'status' => 'Up', 'flow_control' => 'Disabled'],
            ],
        ];
    }

    public function getVlans(string $oltId): array
    {
        return [
            'status' => true,
            'response' => [
                ['vlan_id' => 100, 'description' => 'INTERNET_RESIDENCIAL'],
                ['vlan_id' => 120, 'description' => 'INTERNET_EMPRESARIAL'],
                ['vlan_id' => 200, 'description' => 'IPTV_MULTICAST'],
            ],
        ];
    }

    public function addVlan(string $oltId, array $data): array
    {
        return [
            'status' => true,
            'response' => 'VLAN agregada exitosamente',
            'vlan_id' => $data['vlan_id'] ?? 100,
        ];
    }

    public function getZones(): Collection
    {
        return collect([
            new ZoneData(id: '1', name: 'Centro Historico'),
            new ZoneData(id: '2', name: 'Urbanizacion Santa Victoria'),
            new ZoneData(id: '3', name: 'Pimentel Playa'),
            new ZoneData(id: '4', name: 'La Victoria'),
        ]);
    }

    public function addZone(string $name): ZoneData
    {
        return new ZoneData(
            id: (string) rand(10, 999),
            name: $name,
        );
    }

    public function getOdbs(string $zoneId): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => 'odb_01', 'name' => 'NAP-01', 'zone_id' => $zoneId, 'total_ports' => 16, 'used_ports' => 8],
                ['id' => 'odb_02', 'name' => 'NAP-02', 'zone_id' => $zoneId, 'total_ports' => 16, 'used_ports' => 6],
            ],
        ];
    }

    public function addOdb(array $payload): array
    {
        return [
            'status' => true,
            'response' => 'Splitter registrado exitosamente',
            'odb_id' => 'odb_' . rand(10, 99),
        ];
    }

    public function editOdb(string $odbExternalId, array $payload): array
    {
        return [
            'status' => true,
            'response' => 'Splitter actualizado exitosamente',
        ];
    }

    public function deleteOdb(string $odbExternalId): array
    {
        return [
            'status' => true,
            'response' => 'Splitter eliminado',
        ];
    }

    public function getCabinets(string $zoneId): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => 'cab_01', 'name' => 'Armario Distribucion 01', 'zone_id' => $zoneId],
            ],
        ];
    }

    public function addCabinet(array $payload): array
    {
        return [
            'status' => true,
            'response' => 'Armario registrado',
            'cabinet_id' => 'cab_' . rand(10, 99),
        ];
    }

    public function editCabinet(string $cabinetExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Armario modificado'];
    }

    public function deleteCabinet(string $cabinetExternalId): array
    {
        return ['status' => true, 'response' => 'Armario eliminado'];
    }

    public function getSpeedProfiles(): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => '1', 'name' => 'Plan Hogar 50M', 'download_speed' => '51200', 'upload_speed' => '25600'],
                ['id' => '2', 'name' => 'Plan Duo 100M', 'download_speed' => '102400', 'upload_speed' => '51200'],
                ['id' => '3', 'name' => 'Plan Gamer 200M', 'download_speed' => '204800', 'upload_speed' => '102400'],
                ['id' => '4', 'name' => 'Plan Corporativo 500M', 'download_speed' => '512000', 'upload_speed' => '512000'],
            ],
        ];
    }

    public function getOnuTypes(?string $ponType = null): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => '75', 'name' => 'ZTE-F660V6.0', 'pon_type' => 'gpon', 'ethernet_ports' => 4, 'wifi' => true],
                ['id' => '308', 'name' => 'Huawei-HG8546M', 'pon_type' => 'gpon', 'ethernet_ports' => 4, 'wifi' => true],
                ['id' => '412', 'name' => 'Huawei-EG8145V5', 'pon_type' => 'gpon', 'ethernet_ports' => 4, 'wifi' => true],
            ],
        ];
    }

    public function getAllOnus(int $page = 1, int $pageSize = 100): Collection
    {
        $onus = collect();
        $names = ['Juan Perez', 'Carlos Torres', 'Maria Garcia', 'Rosa Flores', 'Ana Mendoza', 'Luis Quispe'];

        for ($i = 1; $i <= min($pageSize, 25); $i++) {
            $num = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $sn = 'HWTC' . strtoupper(substr(md5("onu-seed-{$i}"), 0, 8));
            $isOffline = ($i === 7 || $i === 19);
            $isWarning = ($i === 4);

            $rx = $isOffline ? null : ($isWarning ? -28.40 : -19.20 - fmod($i * 0.3, 4.0));
            $tx = $isOffline ? null : 2.25;

            $onus->push(new OnuData(
                uniqueExternalId: "onu_hw_{$num}",
                sn: $sn,
                name: $names[$i % count($names)] . " - Abonado {$num}",
                oltId: '1',
                oltName: 'OLT-Principal-Chiclayo',
                board: '0/1',
                port: (string) ((($i - 1) % 4) + 1),
                onuNumber: $i,
                onuTypeId: '308',
                onuTypeName: 'HG8546M',
                zone: $i <= 10 ? 'Centro Historico' : 'Urbanizacion Santa Victoria',
                odb: 'NAP-' . str_pad((string) ((($i - 1) % 8) + 1), 2, '0', STR_PAD_LEFT),
                status: $isOffline ? 'LOS' : 'Online',
                administrativeStatus: 'Enabled',
                catvStatus: null,
                signalValue: $rx !== null ? "{$rx} dBm / {$tx} dBm" : 'N/A',
                signal1310: $tx !== null ? "{$tx} dBm" : 'N/A',
                signal1490: $rx !== null ? "{$rx} dBm" : 'N/A',
                distance: 350 + ($i * 45),
                address: "Calle Los Cedros {$i}0{$i}, Chiclayo",
                latitude: -6.77137 + ($i * 0.001),
                longitude: -79.84088 + ($i * 0.001),
                vlan: 100 + (($i % 3) * 10),
            ));
        }

        return $onus;
    }

    public function getUnconfiguredOnus(?string $oltId = null): Collection
    {
        return collect([
            new OnuData(
                uniqueExternalId: 'unconfigured_01',
                sn: 'ZTEGC7E230E4',
                name: null,
                oltId: $oltId ?? '1',
                oltName: 'OLT-Principal-Chiclayo',
                board: '0/1',
                port: '2',
                onuNumber: 15,
                onuTypeId: '75',
                onuTypeName: 'ZTE-F660V6.0',
                zone: null,
                odb: null,
                status: 'Unconfigured',
                administrativeStatus: 'Disabled',
                catvStatus: null,
                signalValue: '-18.45 dBm / 2.10 dBm',
                signal1310: '2.10 dBm',
                signal1490: '-18.45 dBm',
                distance: 512,
                address: null,
                latitude: null,
                longitude: null,
                vlan: null,
            ),
        ]);
    }

    public function getOnuDetails(string $onuExternalId): ?OnuData
    {
        return $this->getAllOnus()->firstWhere('uniqueExternalId', $onuExternalId);
    }

    public function getOnuDetailsBySn(string $sn): ?OnuData
    {
        return $this->getAllOnus()->firstWhere('sn', $sn);
    }

    public function getOnuSignal(string $onuExternalId): ?OnuSignalData
    {
        return new OnuSignalData(
            onuSignal: 'Very good',
            onuSignalValue: '-19.15 dBm / 2.20 dBm',
            onuSignal1310: '2.20 dBm',
            onuSignal1490: '-19.15 dBm',
        );
    }

    public function getOnuStatus(string $onuExternalId): array
    {
        return [
            'status' => true,
            'onu_status' => 'Online',
            'phase_state' => 'working',
            'last_online' => now()->toIso8601String(),
        ];
    }

    public function getOnuStatusHistory(string $onuExternalId): array
    {
        return [
            'status' => true,
            'response' => [
                ['status' => 'Online', 'date' => now()->subHours(2)->toIso8601String()],
                ['status' => 'LOS', 'date' => now()->subHours(5)->toIso8601String()],
            ],
        ];
    }

    public function getOnuFullStatusInfo(string $onuExternalId): array
    {
        return [
            'status' => true,
            'response' => [
                'status' => 'Online',
                'signal_1490' => '-19.15 dBm',
                'signal_1310' => '2.20 dBm',
                'voltage' => '3.3 V',
                'temperature' => '42.1 C',
                'distance' => '420 m',
                'ip_address' => '100.64.12.8',
            ],
        ];
    }

    public function getOnuRunningConfig(string $onuExternalId): array
    {
        return [
            'status' => true,
            'response' => "ont add 1 15 sn-auth ZTEGC7E230E4 omci ont-lineprofile-id 1 ont-srvprofile-id 1",
        ];
    }

    public function getOnuSignalGraph(string $onuExternalId, string $graphType): array
    {
        return [
            'status' => true,
            'response' => [
                'type' => $graphType,
                'points' => [
                    ['time' => '10:00', 'rx' => -18.9],
                    ['time' => '11:00', 'rx' => -19.1],
                    ['time' => '12:00', 'rx' => -19.0],
                ],
            ],
        ];
    }

    public function getOnuTrafficGraph(string $onuExternalId, string $graphType): array
    {
        return [
            'status' => true,
            'response' => [
                'type' => $graphType,
                'points' => [
                    ['time' => '10:00', 'download_mbps' => 45.2, 'upload_mbps' => 18.4],
                    ['time' => '11:00', 'download_mbps' => 68.1, 'upload_mbps' => 24.3],
                ],
            ],
        ];
    }

    public function getOnuMacs(string $onuExternalId): array
    {
        return [
            'status' => true,
            'response' => [
                ['mac' => 'c8:3a:35:11:22:33', 'vlan' => 100, 'port' => 'eth1'],
            ],
        ];
    }

    public function getOnuRouterHosts(string $onuExternalId): array
    {
        return [
            'status' => true,
            'response' => [
                ['ip' => '192.168.1.100', 'mac' => 'c8:3a:35:11:22:33', 'hostname' => 'iPhone-Juan'],
            ],
        ];
    }

    public function searchByMac(string $macAddress, ?string $oltId = null): array
    {
        return [
            'status' => true,
            'response' => [
                'onu_external_id' => 'onu_hw_001',
                'sn' => 'HWTC1A2B3C4D',
                'mac_address' => $macAddress,
            ],
        ];
    }

    public function getAuthorizationPresets(?string $oltId = null): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => '1', 'name' => 'Preset Residencial 100M', 'vlan' => 100, 'speed_profile' => 'Plan Duo 100M'],
                ['id' => '2', 'name' => 'Preset Corporativo 200M', 'vlan' => 120, 'speed_profile' => 'Plan Gamer 200M'],
            ],
        ];
    }

    public function authorizeOnu(array $payload): array
    {
        return [
            'status' => true,
            'response' => 'ONU autorizada exitosamente',
            'onu_id' => $payload['unique_external_id'] ?? 'onu_hw_new',
        ];
    }

    public function moveOnu(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'ONU reubicada de puerto PON exitosamente'];
    }

    public function updateOnuSn(string $onuExternalId, string $newSn): array
    {
        return ['status' => true, 'response' => 'Número de serie de ONU actualizado'];
    }

    public function changeOnuType(string $onuExternalId, string $newTypeId): array
    {
        return ['status' => true, 'response' => 'Tipo de ONU cambiado'];
    }

    public function updateOnuLocation(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Ubicación física actualizada'];
    }

    public function updateOnuVlan(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'VLAN de ONU actualizada'];
    }

    public function updateOnuMode(string $onuExternalId, string $mode): array
    {
        return ['status' => true, 'response' => "Modo cambiado a {$mode}"];
    }

    public function updateOnuSpeedProfiles(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Perfiles de velocidad asignados exitosamente'];
    }

    public function rebootOnu(string $onuExternalId): bool
    {
        return true;
    }

    public function enableOnu(string $onuExternalId): bool
    {
        return true;
    }

    public function disableOnu(string $onuExternalId): bool
    {
        return true;
    }

    public function deleteOnu(string $onuExternalId): bool
    {
        return true;
    }

    public function bulkEnableOnus(array $onuExternalIds): array
    {
        return [
            'status' => true,
            'response' => 'ONUs habilitadas en lote',
            'count' => count($onuExternalIds),
        ];
    }

    public function bulkDisableOnus(array $onuExternalIds): array
    {
        return [
            'status' => true,
            'response' => 'ONUs deshabilitadas en lote',
            'count' => count($onuExternalIds),
        ];
    }

    public function restoreFactoryDefaults(string $onuExternalId): array
    {
        return ['status' => true, 'response' => 'ONU restaurada a valores de fábrica'];
    }

    public function resyncConfig(string $onuExternalId): array
    {
        return ['status' => true, 'response' => 'Configuración de ONU resincronizada con éxito'];
    }

    public function getExternalTickets(string $onuExternalId, int $limit = 50, int $offset = 0): array
    {
        return [
            'status' => true,
            'response' => [
                ['id' => 'tkt_01', 'onu_id' => $onuExternalId, 'subject' => 'Atenuación alta', 'status' => 'Open'],
            ],
        ];
    }

    public function openExternalTicket(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Ticket de soporte creado', 'ticket_id' => 'tkt_' . rand(100, 999)];
    }

    public function updateExternalTicket(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Ticket de soporte actualizado'];
    }

    public function resolveExternalTicket(string $onuExternalId, array $payload): array
    {
        return ['status' => true, 'response' => 'Ticket de soporte resuelto'];
    }
}
