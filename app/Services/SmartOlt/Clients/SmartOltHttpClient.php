<?php

declare(strict_types=1);

namespace App\Services\SmartOlt\Clients;

use App\Contracts\SmartOlt\SmartOltClientInterface;
use App\DTOs\SmartOlt\OltData;
use App\DTOs\SmartOlt\OnuData;
use App\DTOs\SmartOlt\OnuSignalData;
use App\DTOs\SmartOlt\ZoneData;
use App\Exceptions\SmartOltApiException;
use App\Exceptions\SmartOltRateLimitException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SmartOltHttpClient implements SmartOltClientInterface
{
    protected string $baseUrl;

    protected ?string $apiToken;

    protected int $timeout;

    protected int $retryAttempts;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.smartolt.base_url', 'https://demo.smartolt.com/api');
        $this->apiToken = config('services.smartolt.api_token');
        $this->timeout = (int) config('services.smartolt.timeout', 15);
        $this->retryAttempts = (int) config('services.smartolt.retry_attempts', 3);
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['X-Token' => (string) $this->apiToken])
            ->timeout($this->timeout)
            ->retry($this->retryAttempts, 500, function (\Throwable $exception) {
                return $exception->getCode() === 429;
            }, throw: false);
    }

    protected function validateResponse(Response $response): array
    {
        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('Retry-After') ?: 60);
            throw new SmartOltRateLimitException('Rate limit de SmartOLT alcanzado', $retryAfter);
        }

        if ($response->failed()) {
            throw new SmartOltApiException(
                $response->json('error') ?? "Error en solicitud SmartOLT HTTP {$response->status()}",
                $response->status()
            );
        }

        $json = $response->json();
        if (is_array($json) && isset($json['status']) && $json['status'] === false) {
            throw new SmartOltApiException($json['error'] ?? 'Operación fallida en SmartOLT', 400);
        }

        return $json ?? [];
    }

    public function request(string $method, string $endpoint, array $data = []): array
    {
        $endpoint = '/'.ltrim($endpoint, '/');
        $client = $this->client();

        $response = match (strtoupper($method)) {
            'GET' => $client->get($endpoint, $data),
            'POST' => $client->post($endpoint, $data),
            'PUT' => $client->put($endpoint, $data),
            'DELETE' => $client->delete($endpoint, $data),
            default => throw new SmartOltApiException("Método HTTP no soportado: {$method}", 405),
        };

        return $this->validateResponse($response);
    }

    public function getOlts(): Collection
    {
        $data = $this->request('GET', '/system/get_olts');

        return collect($data['response'] ?? [])
            ->map(fn (array $item) => OltData::fromResponse($item));
    }

    public function getOltDetails(string $oltId): ?OltData
    {
        $data = $this->request('GET', "/system/get_olt_details/{$oltId}");

        if (empty($data['response'])) {
            return null;
        }

        return OltData::fromResponse($data['response']);
    }

    public function getOltUptimeAndTemperature(?string $oltId = null): array
    {
        $endpoint = $oltId
            ? "/olt/get_olt_uptime_and_env_temperature/{$oltId}"
            : '/olt/get_olts_uptime_and_env_temperature';

        return $this->request('GET', $endpoint);
    }

    public function getOltCards(string $oltId): array
    {
        return $this->request('GET', "/system/get_olt_cards_details/{$oltId}");
    }

    public function getOltPonPorts(string $oltId): array
    {
        return $this->request('GET', "/system/get_olt_pon_ports_details/{$oltId}");
    }

    public function getOltOutagePons(string $oltId): array
    {
        return $this->request('GET', "/system/get_outage_pons/{$oltId}");
    }

    public function getOltUplinkPorts(string $oltId): array
    {
        return $this->request('GET', "/system/get_olt_uplink_ports_details/{$oltId}");
    }

    public function getVlans(string $oltId): array
    {
        return $this->request('GET', "/olt/get_vlans/{$oltId}");
    }

    public function addVlan(string $oltId, array $data): array
    {
        return $this->request('POST', "/olt/add_vlan/{$oltId}", $data);
    }

    public function getZones(): Collection
    {
        $data = $this->request('GET', '/system/get_zones');

        return collect($data['response'] ?? [])
            ->map(fn (array $item) => ZoneData::fromResponse($item));
    }

    public function addZone(string $name): ZoneData
    {
        $data = $this->request('POST', '/system/add_zone', ['name' => $name]);

        return ZoneData::fromResponse($data['response'] ?? ['name' => $name]);
    }

    public function getOdbs(string $zoneId): array
    {
        return $this->request('GET', "/system/get_odbs/{$zoneId}");
    }

    public function addOdb(array $payload): array
    {
        return $this->request('POST', '/system/add_odb', $payload);
    }

    public function editOdb(string $odbExternalId, array $payload): array
    {
        return $this->request('POST', "/system/edit_odb/{$odbExternalId}", $payload);
    }

    public function deleteOdb(string $odbExternalId): array
    {
        return $this->request('POST', "/system/delete_odb/{$odbExternalId}");
    }

    public function getCabinets(string $zoneId): array
    {
        return $this->request('GET', "/system/get_cabinets/{$zoneId}");
    }

    public function addCabinet(array $payload): array
    {
        return $this->request('POST', '/system/add_cabinet', $payload);
    }

    public function editCabinet(string $cabinetExternalId, array $payload): array
    {
        return $this->request('POST', "/system/edit_cabinet/{$cabinetExternalId}", $payload);
    }

    public function deleteCabinet(string $cabinetExternalId): array
    {
        return $this->request('POST', "/system/delete_cabinet/{$cabinetExternalId}");
    }

    public function getSpeedProfiles(): array
    {
        return $this->request('GET', '/system/get_speed_profiles');
    }

    public function getOnuTypes(?string $ponType = null): array
    {
        $endpoint = $ponType
            ? "/system/get_onu_types_by_pon_type/{$ponType}"
            : '/system/get_onu_types';

        return $this->request('GET', $endpoint);
    }

    public function getAllOnus(int $page = 1, int $pageSize = 100): Collection
    {
        $data = $this->request('GET', '/onu/get_all_onus_details', [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return collect($data['onus'] ?? $data['response'] ?? [])
            ->map(fn (array $item) => OnuData::fromResponse($item));
    }

    public function getUnconfiguredOnus(?string $oltId = null): Collection
    {
        $endpoint = $oltId
            ? "/onu/unconfigured_onus_for_olt/{$oltId}"
            : '/onu/unconfigured_onus';

        $data = $this->request('GET', $endpoint);

        return collect($data['response'] ?? [])
            ->map(fn (array $item) => OnuData::fromResponse($item));
    }

    public function getOnuDetails(string $onuExternalId): ?OnuData
    {
        $data = $this->request('GET', "/onu/get_onu_details/{$onuExternalId}");

        if (empty($data['response'])) {
            return null;
        }

        return OnuData::fromResponse($data['response']);
    }

    public function getOnuDetailsBySn(string $sn): ?OnuData
    {
        $data = $this->request('GET', "/onu/get_onus_details_by_sn/{$sn}");

        if (empty($data['response'])) {
            return null;
        }

        return OnuData::fromResponse($data['response']);
    }

    public function getOnuSignal(string $onuExternalId): ?OnuSignalData
    {
        $data = $this->request('GET', "/onu/get_onu_signal/{$onuExternalId}");

        return OnuSignalData::fromResponse($data);
    }

    public function getOnuStatus(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_onu_status/{$onuExternalId}");
    }

    public function getOnuStatusHistory(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_onu_status_history/{$onuExternalId}");
    }

    public function getOnuFullStatusInfo(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_onu_full_status_info/{$onuExternalId}");
    }

    public function getOnuRunningConfig(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_running_config/{$onuExternalId}");
    }

    public function getOnuSignalGraph(string $onuExternalId, string $graphType): array
    {
        return $this->request('GET', "/onu/get_onu_signal_graph/{$onuExternalId}/{$graphType}");
    }

    public function getOnuTrafficGraph(string $onuExternalId, string $graphType): array
    {
        return $this->request('GET', "/onu/get_onu_traffic_graph/{$onuExternalId}/{$graphType}");
    }

    public function getOnuMacs(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_onu_macs/{$onuExternalId}");
    }

    public function getOnuRouterHosts(string $onuExternalId): array
    {
        return $this->request('GET', "/onu/get_onu_router_hosts/{$onuExternalId}");
    }

    public function searchByMac(string $macAddress, ?string $oltId = null): array
    {
        $params = ['mac_address' => $macAddress];
        if ($oltId) {
            $params['olt_id'] = $oltId;
        }

        return $this->request('GET', '/onu/search_by_mac', $params);
    }

    public function getAuthorizationPresets(?string $oltId = null): array
    {
        $params = $oltId ? ['olt_id' => $oltId] : [];

        return $this->request('GET', '/onu/get_authorization_presets', $params);
    }

    public function authorizeOnu(array $payload): array
    {
        return $this->request('POST', '/onu/authorize_onu', $payload);
    }

    public function moveOnu(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/move/{$onuExternalId}", $payload);
    }

    public function updateOnuSn(string $onuExternalId, string $newSn): array
    {
        return $this->request('POST', "/onu/update_sn/{$onuExternalId}", ['sn' => $newSn]);
    }

    public function changeOnuType(string $onuExternalId, string $newTypeId): array
    {
        return $this->request('POST', "/onu/change_onu_type/{$onuExternalId}", ['onu_type' => $newTypeId]);
    }

    public function updateOnuLocation(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/update_location_details/{$onuExternalId}", $payload);
    }

    public function updateOnuVlan(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/update_main_vlan/{$onuExternalId}", $payload);
    }

    public function updateOnuMode(string $onuExternalId, string $mode): array
    {
        return $this->request('POST', "/onu/update_onu_mode/{$onuExternalId}", ['mode' => $mode]);
    }

    public function updateOnuSpeedProfiles(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/update_onu_speed_profiles/{$onuExternalId}", $payload);
    }

    public function rebootOnu(string $onuExternalId): bool
    {
        $data = $this->request('POST', "/onu/reboot/{$onuExternalId}");

        return (bool) ($data['status'] ?? false);
    }

    public function enableOnu(string $onuExternalId): bool
    {
        $data = $this->request('POST', "/onu/enable/{$onuExternalId}");

        return (bool) ($data['status'] ?? false);
    }

    public function disableOnu(string $onuExternalId): bool
    {
        $data = $this->request('POST', "/onu/disable/{$onuExternalId}");

        return (bool) ($data['status'] ?? false);
    }

    public function deleteOnu(string $onuExternalId): bool
    {
        $data = $this->request('POST', "/onu/delete/{$onuExternalId}");

        return (bool) ($data['status'] ?? false);
    }

    public function bulkEnableOnus(array $onuExternalIds): array
    {
        return $this->request('POST', '/onu/bulk_enable', ['onus' => $onuExternalIds]);
    }

    public function bulkDisableOnus(array $onuExternalIds): array
    {
        return $this->request('POST', '/onu/bulk_disable', ['onus' => $onuExternalIds]);
    }

    public function restoreFactoryDefaults(string $onuExternalId): array
    {
        return $this->request('POST', "/onu/restore_factory_defaults/{$onuExternalId}");
    }

    public function resyncConfig(string $onuExternalId): array
    {
        return $this->request('POST', "/onu/resync_config/{$onuExternalId}");
    }

    public function getExternalTickets(string $onuExternalId, int $limit = 50, int $offset = 0): array
    {
        return $this->request('GET', "/onu/get_external_tickets/{$onuExternalId}", [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function openExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/open_external_ticket/{$onuExternalId}", $payload);
    }

    public function updateExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/update_external_ticket/{$onuExternalId}", $payload);
    }

    public function resolveExternalTicket(string $onuExternalId, array $payload): array
    {
        return $this->request('POST', "/onu/resolve_external_ticket/{$onuExternalId}", $payload);
    }
}
