<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmartOlt\AuthorizeOnuRequest;
use App\Http\Resources\SmartOlt\OnuResource;
use App\Http\Resources\SmartOlt\OnuSignalResource;
use App\Models\SmartOltOnu;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnuController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', 1));
        $pageSize = min(100, max(1, (int) $request->query('page_size', $request->query('per_page', 20))));

        $query = SmartOltOnu::with(['olt', 'zone', 'odb']);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('olt_id')) {
            $oltId = (string) $request->query('olt_id');
            $query->whereHas('olt', fn ($q) => $q->where('external_id', $oltId));
        }

        if ($request->filled('zone_id')) {
            $zoneId = (string) $request->query('zone_id');
            $query->where('zone_id', $zoneId);
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('sn', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        if ($query->count() > 0) {
            $paginator = $query->paginate($pageSize, ['*'], 'page', $page);
            return ApiResponse::onus(
                OnuResource::collection($paginator->items()),
                [
                    'total' => $paginator->total(),
                    'page' => $paginator->currentPage(),
                    'page_size' => $paginator->perPage(),
                    'total_pages' => $paginator->lastPage(),
                    'has_more' => $paginator->hasMorePages(),
                ]
            );
        }

        $onus = $this->smartOltService->getAllOnus($page, $pageSize);
        return ApiResponse::onus(
            OnuResource::collection($onus),
            [
                'total' => $onus->count(),
                'page' => $page,
                'page_size' => $pageSize,
                'total_pages' => 1,
                'has_more' => false,
            ]
        );
    }

    public function mapPoints(Request $request): JsonResponse
    {
        $query = SmartOltOnu::query()
            ->select(['id', 'unique_external_id', 'sn', 'name', 'latitude', 'longitude', 'status', 'signal_rx', 'odb_id', 'zone_id'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled(['north', 'south', 'east', 'west'])) {
            $north = (float) $request->query('north');
            $south = (float) $request->query('south');
            $east = (float) $request->query('east');
            $west = (float) $request->query('west');

            $query->whereBetween('latitude', [min($south, $north), max($south, $north)])
                ->whereBetween('longitude', [min($west, $east), max($west, $east)]);
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', (string) $request->query('zone_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $limit = min(2000, max(1, (int) $request->query('limit', 1000)));
        $points = $query->limit($limit)->get();

        return ApiResponse::success($points);
    }

    public function unconfigured(Request $request): JsonResponse
    {
        $oltId = $request->query('olt_id');
        $onus = $this->smartOltService->getUnconfiguredOnus($oltId ? (string) $oltId : null);

        return ApiResponse::success(OnuResource::collection($onus));
    }

    public function show(string $id): JsonResponse
    {
        $onuFromDb = SmartOltOnu::with(['olt', 'zone', 'odb'])
            ->where('unique_external_id', $id)
            ->first();

        if ($onuFromDb !== null) {
            return ApiResponse::success(new OnuResource($onuFromDb));
        }

        $onu = $this->smartOltService->getOnuDetails($id);

        if ($onu === null) {
            return ApiResponse::notFound('ONU');
        }

        return ApiResponse::success(new OnuResource($onu));
    }

    public function showBySn(string $sn): JsonResponse
    {
        $onuFromDb = SmartOltOnu::with(['olt', 'zone', 'odb'])
            ->where('sn', $sn)
            ->first();

        if ($onuFromDb !== null) {
            return ApiResponse::success(new OnuResource($onuFromDb));
        }

        $onu = $this->smartOltService->getOnuDetailsBySn($sn);

        if ($onu === null) {
            return ApiResponse::notFound('ONU con serial indicado');
        }

        return ApiResponse::success(new OnuResource($onu));
    }

    public function signal(string $id): JsonResponse
    {
        $signal = $this->smartOltService->getOnuSignal($id);

        if ($signal === null) {
            return ApiResponse::notFound('Señal óptica');
        }

        $resource = new OnuSignalResource($signal);
        return ApiResponse::signal($resource->resolve());
    }

    public function status(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuStatus($id));
    }

    public function statusHistory(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuStatusHistory($id));
    }

    public function fullStatus(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuFullStatusInfo($id));
    }

    public function runningConfig(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuRunningConfig($id));
    }

    public function signalGraph(string $id, string $graphType): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuSignalGraph($id, $graphType));
    }

    public function trafficGraph(string $id, string $graphType): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuTrafficGraph($id, $graphType));
    }

    public function macs(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuMacs($id));
    }

    public function routerHosts(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOnuRouterHosts($id));
    }

    public function searchByMac(Request $request): JsonResponse
    {
        $request->validate(['mac_address' => ['required', 'string']]);
        $oltId = $request->query('olt_id');

        return ApiResponse::raw(
            $this->smartOltService->searchByMac(
                (string) $request->query('mac_address'),
                $oltId ? (string) $oltId : null
            )
        );
    }

    public function authorizationPresets(Request $request): JsonResponse
    {
        $oltId = $request->query('olt_id');
        return ApiResponse::raw(
            $this->smartOltService->getAuthorizationPresets($oltId ? (string) $oltId : null)
        );
    }

    public function authorize(AuthorizeOnuRequest $request): JsonResponse
    {
        $result = $this->smartOltService->authorizeOnu($request->validated());
        return ApiResponse::raw($result, 201);
    }

    public function move(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'board' => ['required', 'string'],
            'port' => ['required', 'string'],
        ]);

        return ApiResponse::raw($this->smartOltService->moveOnu($id, $request->all()));
    }

    public function updateSn(string $id, Request $request): JsonResponse
    {
        $request->validate(['sn' => ['required', 'string', 'size:12']]);
        return ApiResponse::raw($this->smartOltService->updateOnuSn($id, (string) $request->input('sn')));
    }

    public function changeType(string $id, Request $request): JsonResponse
    {
        $request->validate(['onu_type' => ['required', 'string']]);
        return ApiResponse::raw($this->smartOltService->changeOnuType($id, (string) $request->input('onu_type')));
    }

    public function updateLocation(string $id, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->updateOnuLocation($id, $request->all()));
    }

    public function updateVlan(string $id, Request $request): JsonResponse
    {
        $request->validate(['vlan' => ['required', 'integer', 'min:1', 'max:4094']]);
        return ApiResponse::raw($this->smartOltService->updateOnuVlan($id, $request->all()));
    }

    public function updateMode(string $id, Request $request): JsonResponse
    {
        $request->validate(['mode' => ['required', 'string', 'in:Routing,Bridging']]);
        return ApiResponse::raw($this->smartOltService->updateOnuMode($id, (string) $request->input('mode')));
    }

    public function updateSpeedProfiles(string $id, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->updateOnuSpeedProfiles($id, $request->all()));
    }

    public function reboot(string $id): JsonResponse
    {
        $success = $this->smartOltService->rebootOnu($id);
        return ApiResponse::raw([
            'status' => $success,
            'response' => $success ? 'ONU reiniciada correctamente' : 'Fallo al reiniciar ONU',
        ]);
    }

    public function enable(string $id): JsonResponse
    {
        $success = $this->smartOltService->enableOnu($id);
        return ApiResponse::raw([
            'status' => $success,
            'response' => $success ? 'ONU habilitada' : 'Fallo al habilitar ONU',
        ]);
    }

    public function disable(string $id): JsonResponse
    {
        $success = $this->smartOltService->disableOnu($id);
        return ApiResponse::raw([
            'status' => $success,
            'response' => $success ? 'ONU deshabilitada' : 'Fallo al deshabilitar ONU',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $success = $this->smartOltService->deleteOnu($id);
        return ApiResponse::raw([
            'status' => $success,
            'response' => $success ? 'ONU eliminada exitosamente' : 'Fallo al eliminar ONU',
        ]);
    }

    public function bulkEnable(Request $request): JsonResponse
    {
        $request->validate(['onus' => ['required', 'array']]);
        return ApiResponse::raw($this->smartOltService->bulkEnableOnus($request->input('onus')));
    }

    public function bulkDisable(Request $request): JsonResponse
    {
        $request->validate(['onus' => ['required', 'array']]);
        return ApiResponse::raw($this->smartOltService->bulkDisableOnus($request->input('onus')));
    }

    public function restoreDefaults(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->restoreFactoryDefaults($id));
    }

    public function resyncConfig(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->resyncConfig($id));
    }

    public function tickets(string $id, Request $request): JsonResponse
    {
        $limit = max(1, (int) $request->query('limit', 50));
        $offset = max(0, (int) $request->query('offset', 0));

        return ApiResponse::raw($this->smartOltService->getExternalTickets($id, $limit, $offset));
    }

    public function openTicket(string $id, Request $request): JsonResponse
    {
        $request->validate(['subject' => ['required', 'string', 'max:255']]);
        return ApiResponse::raw($this->smartOltService->openExternalTicket($id, $request->all()), 201);
    }

    public function updateTicket(string $id, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->updateExternalTicket($id, $request->all()));
    }

    public function resolveTicket(string $id, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->resolveExternalTicket($id, $request->all()));
    }
}
