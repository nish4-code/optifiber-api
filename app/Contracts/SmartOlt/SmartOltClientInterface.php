<?php

declare(strict_types=1);

namespace App\Contracts\SmartOlt;

use App\DTOs\SmartOlt\OltData;
use App\DTOs\SmartOlt\OnuData;
use App\DTOs\SmartOlt\OnuSignalData;
use App\DTOs\SmartOlt\ZoneData;
use Illuminate\Support\Collection;

interface SmartOltClientInterface
{
    /**
     * @return Collection<int, OltData>
     */
    public function getOlts(): Collection;

    public function getOltDetails(string $oltId): ?OltData;

    public function getOltUptimeAndTemperature(?string $oltId = null): array;

    public function getOltCards(string $oltId): array;

    public function getOltPonPorts(string $oltId): array;

    public function getOltOutagePons(string $oltId): array;

    public function getOltUplinkPorts(string $oltId): array;

    public function getVlans(string $oltId): array;

    public function addVlan(string $oltId, array $data): array;

    /**
     * @return Collection<int, ZoneData>
     */
    public function getZones(): Collection;

    public function addZone(string $name): ZoneData;

    public function getOdbs(string $zoneId): array;

    public function addOdb(array $payload): array;

    public function editOdb(string $odbExternalId, array $payload): array;

    public function deleteOdb(string $odbExternalId): array;

    public function getCabinets(string $zoneId): array;

    public function addCabinet(array $payload): array;

    public function editCabinet(string $cabinetExternalId, array $payload): array;

    public function deleteCabinet(string $cabinetExternalId): array;

    public function getSpeedProfiles(): array;

    public function getOnuTypes(?string $ponType = null): array;

    /**
     * @return Collection<int, OnuData>
     */
    public function getAllOnus(int $page = 1, int $pageSize = 100): Collection;

    /**
     * @return Collection<int, OnuData>
     */
    public function getUnconfiguredOnus(?string $oltId = null): Collection;

    public function getOnuDetails(string $onuExternalId): ?OnuData;

    public function getOnuDetailsBySn(string $sn): ?OnuData;

    public function getOnuSignal(string $onuExternalId): ?OnuSignalData;

    public function getOnuStatus(string $onuExternalId): array;

    public function getOnuStatusHistory(string $onuExternalId): array;

    public function getOnuFullStatusInfo(string $onuExternalId): array;

    public function getOnuRunningConfig(string $onuExternalId): array;

    public function getOnuSignalGraph(string $onuExternalId, string $graphType): array;

    public function getOnuTrafficGraph(string $onuExternalId, string $graphType): array;

    public function getOnuMacs(string $onuExternalId): array;

    public function getOnuRouterHosts(string $onuExternalId): array;

    public function searchByMac(string $macAddress, ?string $oltId = null): array;

    public function getAuthorizationPresets(?string $oltId = null): array;

    public function authorizeOnu(array $payload): array;

    public function moveOnu(string $onuExternalId, array $payload): array;

    public function updateOnuSn(string $onuExternalId, string $newSn): array;

    public function changeOnuType(string $onuExternalId, string $newTypeId): array;

    public function updateOnuLocation(string $onuExternalId, array $payload): array;

    public function updateOnuVlan(string $onuExternalId, array $payload): array;

    public function updateOnuMode(string $onuExternalId, string $mode): array;

    public function updateOnuSpeedProfiles(string $onuExternalId, array $payload): array;

    public function rebootOnu(string $onuExternalId): bool;

    public function enableOnu(string $onuExternalId): bool;

    public function disableOnu(string $onuExternalId): bool;

    public function deleteOnu(string $onuExternalId): bool;

    public function bulkEnableOnus(array $onuExternalIds): array;

    public function bulkDisableOnus(array $onuExternalIds): array;

    public function restoreFactoryDefaults(string $onuExternalId): array;

    public function resyncConfig(string $onuExternalId): array;

    public function getExternalTickets(string $onuExternalId, int $limit = 50, int $offset = 0): array;

    public function openExternalTicket(string $onuExternalId, array $payload): array;

    public function updateExternalTicket(string $onuExternalId, array $payload): array;

    public function resolveExternalTicket(string $onuExternalId, array $payload): array;

    public function request(string $method, string $endpoint, array $data = []): array;
}
