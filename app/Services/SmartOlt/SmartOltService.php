<?php

namespace App\Services\SmartOlt;

use App\Models\SmartOltOlt;
use App\Models\SmartOltOnu;
use App\Services\NetworkAlertService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartOltService
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected bool $mockMode;

    protected int $cacheTtl;

    protected SmartOltMockClient $mockClient;

    protected NetworkAlertService $alertService;

    public function __construct(SmartOltMockClient $mockClient, NetworkAlertService $alertService)
    {
        $this->baseUrl = config('services.smartolt.base_url', 'https://demo.smartolt.com/api');
        $this->apiKey = config('services.smartolt.api_key');
        $this->mockMode = (bool) config('services.smartolt.mock_mode', true) || empty($this->apiKey);
        $this->cacheTtl = (int) config('services.smartolt.cache_ttl', 300);
        $this->mockClient = $mockClient;
        $this->alertService = $alertService;
    }

    public function isMockMode(): bool
    {
        return $this->mockMode;
    }

    public function getOlts(): array
    {
        if ($this->mockMode) {
            return $this->mockClient->getOlts();
        }

        return Cache::remember('smartolt:olts', $this->cacheTtl, function () {
            $response = Http::withHeaders(['X-Token' => $this->apiKey])
                ->timeout(10)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/system/get_olts");

            if ($response->successful()) {
                return $response->json('response') ?? [];
            }

            Log::error('SmartOLT Error al consultar OLTs', ['status' => $response->status()]);

            return [];
        });
    }

    public function getOnus(int $page = 1, int $pageSize = 100): array
    {
        if ($this->mockMode) {
            return $this->mockClient->getOnus();
        }

        $cacheKey = "smartolt:onus:p{$page}:s{$pageSize}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page, $pageSize) {
            $response = Http::withHeaders(['X-Token' => $this->apiKey])
                ->timeout(15)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/onu/get_all_onus_details", [
                    'page' => $page,
                    'page_size' => $pageSize,
                ]);

            if ($response->successful()) {
                return $response->json('onus') ?? $response->json('response') ?? [];
            }

            Log::error('SmartOLT Error al consultar ONUs', ['status' => $response->status()]);

            return [];
        });
    }

    public function getOnuSignal(string $externalId): ?array
    {
        if ($this->mockMode) {
            return $this->mockClient->getOnuSignal($externalId);
        }

        return Cache::remember("smartolt:signal:{$externalId}", 60, function () use ($externalId) {
            $response = Http::withHeaders(['X-Token' => $this->apiKey])
                ->timeout(8)
                ->get("{$this->baseUrl}/onu/get_onu_signal/{$externalId}");

            if ($response->successful()) {
                return $response->json('response') ?? $response->json();
            }

            return null;
        });
    }

    public function syncToDatabase(): array
    {
        $olts = $this->getOlts();
        $syncedOlts = 0;
        $oltIdMap = [];

        foreach ($olts as $item) {
            $olt = SmartOltOlt::updateOrCreate(
                ['external_id' => $item['id']],
                [
                    'name' => $item['name'] ?? 'OLT',
                    'ip' => $item['ip'] ?? null,
                    'hardware_version' => $item['hardware_version'] ?? null,
                    'status' => $item['status'] ?? 'online',
                    'metrics' => $item['metrics'] ?? null,
                    'last_synced_at' => now(),
                ]
            );
            $oltIdMap[$item['id']] = $olt->id;
            $syncedOlts++;
        }

        $onus = $this->getOnus();
        $syncedOnus = 0;

        foreach ($onus as $item) {
            $oltDbId = $oltIdMap[$item['olt_id'] ?? ''] ?? SmartOltOlt::first()?->id;

            $onu = SmartOltOnu::updateOrCreate(
                ['external_id' => $item['id']],
                [
                    'sn' => $item['sn'],
                    'name' => $item['name'] ?? null,
                    'smartolt_olt_id' => $oltDbId,
                    'board' => $item['board'] ?? null,
                    'port' => $item['port'] ?? null,
                    'onu_number' => $item['onu_number'] ?? null,
                    'zone' => $item['zone'] ?? null,
                    'odb' => $item['odb'] ?? null,
                    'status' => $item['status'] ?? 'Online',
                    'signal_rx' => $item['signal_rx'] ?? null,
                    'signal_tx' => $item['signal_tx'] ?? null,
                    'distance_m' => $item['distance_m'] ?? null,
                    'last_synced_at' => now(),
                ]
            );

            $this->alertService->evaluateOnuSignal($onu);
            $syncedOnus++;
        }

        $this->alertService->syncAllAlerts();

        return [
            'mode' => $this->mockMode ? 'mock' : 'live',
            'olts_synced' => $syncedOlts,
            'onus_synced' => $syncedOnus,
            'synced_at' => now()->toIso8601String(),
        ];
    }
}
