<?php

declare(strict_types=1);

namespace App\Services\SmartOlt;

use App\Contracts\SmartOlt\SmartOltClientInterface;
use App\DTOs\SmartOlt\OltData;
use App\DTOs\SmartOlt\OnuData;
use App\DTOs\SmartOlt\OnuSignalData;
use App\DTOs\SmartOlt\ZoneData;
use App\Models\SmartOltOlt;
use App\Models\SmartOltOnu;
use App\Models\SmartOltZone;
use App\Services\NetworkAlertService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SmartOltService
{
    protected int $cacheTtl;

    public function __construct(
        protected readonly SmartOltClientInterface $client,
        protected readonly NetworkAlertService $alertService
    ) {
        $this->cacheTtl = (int) config('services.smartolt.cache_ttl', 300);
    }

    public function isMockMode(): bool
    {
        return (bool) config('services.smartolt.mock_mode', true)
            || config('services.smartolt.mode', 'development') !== 'production'
            || empty(config('services.smartolt.api_token') ?: config('services.smartolt.api_key'));
    }

    public function getOlts(): Collection
    {
        return Cache::remember('smartolt:olts', $this->cacheTtl, function () {
            return $this->client->getOlts();
        });
    }

    public function getOltDetails(string $oltId): ?OltData
    {
        return Cache::remember("smartolt:olt:{$oltId}", $this->cacheTtl, function () use ($oltId) {
            return $this->client->getOltDetails($oltId);
        });
    }

    public function getOltUptimeAndTemperature(?string $oltId = null): array
    {
        return $this->client->getOltUptimeAndTemperature($oltId);
    }

    public function getOltCards(string $oltId): array
    {
        return $this->client->getOltCards($oltId);
    }

    public function getOltPonPorts(string $oltId): array
    {
        return $this->client->getOltPonPorts($oltId);
    }

    public function getOltOutagePons(string $oltId): array
    {
        return $this->client->getOltOutagePons($oltId);
    }

    public function getOltUplinkPorts(string $oltId): array
    {
        return $this->client->getOltUplinkPorts($oltId);
    }

    public function getVlans(string $oltId): array
    {
        return $this->client->getVlans($oltId);
    }

    public function addVlan(string $oltId, array $data): array
    {
        return $this->client->addVlan($oltId, $data);
    }

    public function getZones(): Collection
    {
        return Cache::remember('smartolt:zones', $this->cacheTtl, function () {
            return $this->client->getZones();
        });
    }

    public function addZone(string $name): ZoneData
    {
        Cache::forget('smartolt:zones');
        $zoneData = $this->client->addZone($name);

        SmartOltZone::updateOrCreate(
            ['external_id' => $zoneData->id],
            ['name' => $zoneData->name]
        );

        return $zoneData;
    }

    public function getOdbs(string $zoneId): array
    {
        return $this->client->getOdbs($zoneId);
    }

    public function addOdb(array $payload): array
    {
        return $this->client->addOdb($payload);
    }

    public function editOdb(string $odbExternalId, array $payload): array
    {
        return $this->client->editOdb($odbExternalId, $payload);
    }

    public function deleteOdb(string $odbExternalId): array
    {
        return $this->client->deleteOdb($odbExternalId);
    }

    public function getCabinets(string $zoneId): array
    {
        return $this->client->getCabinets($zoneId);
    }

    public function addCabinet(array $payload): array
    {
        return $this->client->addCabinet($payload);
    }

    public function editCabinet(string $cabinetExternalId, array $payload): array
    {
        return $this->client->editCabinet($cabinetExternalId, $payload);
    }

    public function deleteCabinet(string $cabinetExternalId): array
    {
        return $this->client->deleteCabinet($cabinetExternalId);
    }

    public function getSpeedProfiles(): array
    {
        return $this->client->getSpeedProfiles();
    }

    public function getOnuTypes(?string $ponType = null): array
    {
        return $this->client->getOnuTypes($ponType);
    }

    public function getAllOnus(int $page = 1, int $pageSize = 100): Collection
    {
        $cacheKey = "smartolt:onus:p{$page}:s{$pageSize}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page, $pageSize) {
            return $this->client->getAllOnus($page, $pageSize);
        });
    }

    public function getUnconfiguredOnus(?string $oltId = null): Collection
    {
        return $this->client->getUnconfiguredOnus($oltId);
    }

    public function getOnuDetails(string $onuExternalId): ?OnuData
    {
        return Cache::remember("smartolt:onu:{$onuExternalId}", $this->cacheTtl, function () use ($onuExternalId) {
            return $this->client->getOnuDetails($onuExternalId);
        });
    }

    public function getOnuDetailsBySn(string $sn): ?OnuData
    {
        return $this->client->getOnuDetailsBySn($sn);
    }

    public function getOnuSignal(string $onuExternalId): ?OnuSignalData
    {
        return Cache::remember("smartolt:signal:{$onuExternalId}", 60, function () use ($onuExternalId) {
            return $this->client->getOnuSignal($onuExternalId);
        });
    }

    public function getOnuStatus(string $onuExternalId): array
    {
        return $this->client->getOnuStatus($onuExternalId);
    }

    public function getOnuStatusHistory(string $onuExternalId): array
    {
        return $this->client->getOnuStatusHistory($onuExternalId);
    }

    public function getOnuFullStatusInfo(string $onuExternalId): array
    {
        return $this->client->getOnuFullStatusInfo($onuExternalId);
    }

    public function getOnuRunningConfig(string $onuExternalId): array
    {
        return $this->client->getOnuRunningConfig($onuExternalId);
    }

    public function getOnuSignalGraph(string $onuExternalId, string $graphType): array
    {
        return $this->client->getOnuSignalGraph($onuExternalId, $graphType);
    }

    public function getOnuTrafficGraph(string $onuExternalId, string $graphType): array
    {
        return $this->client->getOnuTrafficGraph($onuExternalId, $graphType);
    }

    public function getOnuMacs(string $onuExternalId): array
    {
        return $this->client->getOnuMacs($onuExternalId);
    }

    public function getOnuRouterHosts(string $onuExternalId): array
    {
        return $this->client->getOnuRouterHosts($onuExternalId);
    }

    public function searchByMac(string $macAddress, ?string $oltId = null): array
    {
        return $this->client->searchByMac($macAddress, $oltId);
    }

    public function getAuthorizationPresets(?string $oltId = null): array
    {
        return $this->client->getAuthorizationPresets($oltId);
    }

    public function authorizeOnu(array $payload): array
    {
        Cache::forget('smartolt:onus:p1:s100');

        return $this->client->authorizeOnu($payload);
    }

    public function moveOnu(string $onuExternalId, array $payload): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->moveOnu($onuExternalId, $payload);
    }

    public function updateOnuSn(string $onuExternalId, string $newSn): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->updateOnuSn($onuExternalId, $newSn);
    }

    public function changeOnuType(string $onuExternalId, string $newTypeId): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->changeOnuType($onuExternalId, $newTypeId);
    }

    public function updateOnuLocation(string $onuExternalId, array $payload): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->updateOnuLocation($onuExternalId, $payload);
    }

    public function updateOnuVlan(string $onuExternalId, array $payload): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->updateOnuVlan($onuExternalId, $payload);
    }

    public function updateOnuMode(string $onuExternalId, string $mode): array
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");

        return $this->client->updateOnuMode($onuExternalId, $mode);
    }

    public function updateOnuSpeedProfiles(string $onuExternalId, array $payload): array
    {
        return $this->client->updateOnuSpeedProfiles($onuExternalId, $payload);
    }

    public function rebootOnu(string $onuExternalId): bool
    {
        return $this->client->rebootOnu($onuExternalId);
    }

    public function enableOnu(string $onuExternalId): bool
    {
        return $this->client->enableOnu($onuExternalId);
    }

    public function disableOnu(string $onuExternalId): bool
    {
        return $this->client->disableOnu($onuExternalId);
    }

    public function deleteOnu(string $onuExternalId): bool
    {
        Cache::forget("smartolt:onu:{$onuExternalId}");
        Cache::forget('smartolt:onus:p1:s100');
        SmartOltOnu::where('unique_external_id', $onuExternalId)->delete();

        return $this->client->deleteOnu($onuExternalId);
    }

    public function bulkEnableOnus(array $onuExternalIds): array
    {
        return $this->client->bulkEnableOnus($onuExternalIds);
    }

    public function bulkDisableOnus(array $onuExternalIds): array
    {
        return $this->client->bulkDisableOnus($onuExternalIds);
    }

    public function restoreFactoryDefaults(string $onuExternalId): array
    {
        return $this->client->restoreFactoryDefaults($onuExternalId);
    }

    public function resyncConfig(string $onuExternalId): array
    {
        return $this->client->resyncConfig($onuExternalId);
    }

    public function getExternalTickets(string $onuExternalId, int $limit = 50, int $offset = 0): array
    {
        return $this->client->getExternalTickets($onuExternalId, $limit, $offset);
    }

    public function openExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->client->openExternalTicket($onuExternalId, $payload);
    }

    public function updateExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->client->updateExternalTicket($onuExternalId, $payload);
    }

    public function resolveExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->client->resolveExternalTicket($onuExternalId, $payload);
    }

    public function syncToDatabase(): array
    {
        $olts = $this->getOlts();
        $syncedOlts = 0;
        $oltDbMap = [];

        foreach ($olts as $oltData) {
            $olt = SmartOltOlt::updateOrCreate(
                ['external_id' => $oltData->id],
                [
                    'name' => $oltData->name,
                    'hardware_version' => $oltData->hardwareVersion,
                    'ip' => $oltData->ip,
                    'telnet_port' => $oltData->telnetPort,
                    'snmp_port' => $oltData->snmpPort,
                    'status' => $oltData->status,
                    'metrics' => $oltData->metrics,
                    'last_synced_at' => now(),
                ]
            );
            $oltDbMap[$oltData->id] = $olt->id;
            $syncedOlts++;
        }

        $zones = $this->getZones();
        $syncedZones = 0;
        $zoneDbMap = [];

        foreach ($zones as $zoneData) {
            $zone = SmartOltZone::updateOrCreate(
                ['external_id' => $zoneData->id],
                ['name' => $zoneData->name]
            );
            $zoneDbMap[$zoneData->name] = $zone->id;
            $syncedZones++;
        }

        $onus = $this->getAllOnus(1, 100);
        $syncedOnus = 0;

        foreach ($onus as $onuData) {
            $oltDbId = $oltDbMap[$onuData->oltId ?? ''] ?? SmartOltOlt::first()?->id;
            $zoneDbId = $zoneDbMap[$onuData->zone ?? ''] ?? null;

            $signalRx = $this->parseSignalDbm($onuData->signal1490);
            $signalTx = $this->parseSignalDbm($onuData->signal1310);

            $onu = SmartOltOnu::updateOrCreate(
                ['external_id' => $onuData->uniqueExternalId],
                [
                    'sn' => $onuData->sn,
                    'name' => $onuData->name,
                    'smartolt_olt_id' => $oltDbId,
                    'zone_id' => $zoneDbId,
                    'zone' => $onuData->zone,
                    'odb' => $onuData->odb,
                    'board' => $onuData->board,
                    'port' => $onuData->port,
                    'onu_number' => $onuData->onuNumber,
                    'onu_type_id' => $onuData->onuTypeId,
                    'onu_type_name' => $onuData->onuTypeName,
                    'status' => $onuData->status,
                    'administrative_status' => $onuData->administrativeStatus ?? 'Enabled',
                    'catv_status' => $onuData->catvStatus,
                    'signal_value' => $onuData->signalValue,
                    'signal_1310' => $onuData->signal1310,
                    'signal_1490' => $onuData->signal1490,
                    'signal_rx' => $signalRx,
                    'signal_tx' => $signalTx,
                    'distance_m' => $onuData->distance,
                    'address' => $onuData->address,
                    'latitude' => $onuData->latitude,
                    'longitude' => $onuData->longitude,
                    'vlan' => $onuData->vlan,
                    'last_synced_at' => now(),
                ]
            );

            $this->alertService->evaluateOnuSignal($onu);
            $syncedOnus++;
        }

        return [
            'mode' => $this->isMockMode() ? 'mock' : 'live',
            'olts_synced' => $syncedOlts,
            'zones_synced' => $syncedZones,
            'onus_synced' => $syncedOnus,
            'synced_at' => now()->toIso8601String(),
        ];
    }

    private function parseSignalDbm(?string $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', $raw, $matches) === 1) {
            return (float) $matches[0];
        }

        return null;
    }
}
