<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmartOlt\AuthorizeOnuRequest;
use App\Http\Requests\SmartOlt\BulkOnusRequest;
use App\Http\Requests\SmartOlt\ChangeOnuTypeRequest;
use App\Http\Requests\SmartOlt\MoveOnuRequest;
use App\Http\Requests\SmartOlt\OpenTicketRequest;
use App\Http\Requests\SmartOlt\ResolveTicketRequest;
use App\Http\Requests\SmartOlt\SearchMacRequest;
use App\Http\Requests\SmartOlt\UpdateOnuLocationRequest;
use App\Http\Requests\SmartOlt\UpdateOnuModeRequest;
use App\Http\Requests\SmartOlt\UpdateOnuSnRequest;
use App\Http\Requests\SmartOlt\UpdateOnuSpeedProfilesRequest;
use App\Http\Requests\SmartOlt\UpdateOnuVlanRequest;
use App\Http\Requests\SmartOlt\UpdateTicketRequest;
use App\Http\Resources\SmartOlt\OnuResource;
use App\Models\SmartOltOnu;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnuController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function mapPoints(Request $request): JsonResponse
    {
        $query = SmartOltOnu::query()
            ->select(['id', 'external_id as unique_external_id', 'sn', 'name', 'latitude', 'longitude', 'status', 'signal_rx', 'odb_id', 'zone_id'])
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
            $query->where('zone_id', (int) $request->query('zone_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $limit = min(2000, max(1, (int) $request->query('limit', 1000)));
        $points = $query->limit($limit)->get();

        return ApiResponseHelper::returnSuccess('Puntos GIS de ONUs obtenidos.', $points)->send();
    }

    public function unconfigured(Request $request): JsonResponse
    {
        $oltId = $request->query('olt_id');
        $onus = $this->smartOltService->getUnconfiguredOnus($oltId ? (string) $oltId : null);

        return ApiResponseHelper::returnSuccess('ONUs sin configurar obtenidas.', OnuResource::collection($onus))->send();
    }

    public function show(string $id): JsonResponse
    {
        $onuFromDb = SmartOltOnu::with(['olt', 'zoneRecord', 'odbBox'])
            ->where('external_id', $id)
            ->first();

        if ($onuFromDb !== null) {
            return ApiResponseHelper::returnSuccess('Detalle de ONU obtenido.', new OnuResource($onuFromDb))->send();
        }

        $onu = $this->smartOltService->getOnuDetails($id);

        if ($onu === null) {
            return ApiResponseHelper::notFound('ONU')->send();
        }

        return ApiResponseHelper::returnSuccess('Detalle de ONU obtenido.', new OnuResource($onu))->send();
    }

    public function trace(string $id): JsonResponse
    {
        $onu = SmartOltOnu::with(['odbBox.olt', 'odbBox.zone', 'olt'])
            ->where('external_id', $id)
            ->when(ctype_digit($id), fn ($q) => $q->orWhere('id', (int) $id))
            ->first();

        if ($onu === null) {
            return ApiResponseHelper::notFound('ONU')->send();
        }

        $odb = $onu->odbBox;
        $olt = $onu->olt ?? $odb?->olt;

        $hops = [];
        $polyline = [];

        if ($onu->latitude && $onu->longitude) {
            $hops[] = [
                'type' => 'onu',
                'id' => $onu->id,
                'unique_external_id' => $onu->external_id,
                'label' => $onu->name ?? $onu->sn,
                'status' => $onu->status,
                'signal_rx' => $onu->signal_rx,
                'address' => $onu->address,
                'latitude' => $onu->latitude,
                'longitude' => $onu->longitude,
            ];
            $polyline[] = [$onu->latitude, $onu->longitude];
        }

        if ($odb) {
            $napLat = $odb->latitude ?? ($onu->latitude ? $onu->latitude + 0.0015 : null);
            $napLng = $odb->longitude ?? ($onu->longitude ? $onu->longitude + 0.0012 : null);

            $hops[] = [
                'type' => 'odb',
                'id' => $odb->id,
                'external_id' => $odb->external_id,
                'label' => $odb->name,
                'pon_port' => $odb->pon_port,
                'total_ports' => $odb->total_ports,
                'used_ports' => $odb->used_ports,
                'zone' => $odb->zone?->name,
                'latitude' => $napLat,
                'longitude' => $napLng,
            ];

            if ($napLat && $napLng) {
                $polyline[] = [$napLat, $napLng];
            }
        }

        if ($olt) {
            $oltLat = $olt->latitude ?? -6.76200;
            $oltLng = $olt->longitude ?? -79.83500;

            $hops[] = [
                'type' => 'olt',
                'id' => $olt->id,
                'external_id' => $olt->external_id,
                'label' => $olt->name,
                'ip' => $olt->ip,
                'hardware' => $olt->hardware_version,
                'board' => $onu->board,
                'port' => $onu->port,
                'onu_number' => $onu->onu_number,
                'latitude' => $oltLat,
                'longitude' => $oltLng,
            ];

            if ($oltLat && $oltLng) {
                $polyline[] = [$oltLat, $oltLng];
            }
        }

        return ApiResponseHelper::returnSuccess('Trazado óptico de ONU obtenido.', [
            'onu_id' => $onu->external_id,
            'sn' => $onu->sn,
            'status' => $onu->status,
            'distance_m' => $onu->distance_m,
            'hops' => $hops,
            'polyline' => $polyline,
        ])->send();
    }

    public function showBySn(string $sn): JsonResponse
    {
        $onuFromDb = SmartOltOnu::with(['olt', 'zoneRecord', 'odbBox'])
            ->where('sn', $sn)
            ->first();

        if ($onuFromDb !== null) {
            return ApiResponseHelper::returnSuccess('Detalle de ONU obtenido.', new OnuResource($onuFromDb))->send();
        }

        $onu = $this->smartOltService->getOnuDetailsBySn($sn);

        if ($onu === null) {
            return ApiResponseHelper::notFound('ONU con serial indicado')->send();
        }

        return ApiResponseHelper::returnSuccess('Detalle de ONU obtenido.', new OnuResource($onu))->send();
    }

    public function status(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Estado de ONU obtenido.', $this->smartOltService->getOnuStatus($id))->send();
    }

    public function statusHistory(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Historial de estado obtenido.', $this->smartOltService->getOnuStatusHistory($id))->send();
    }

    public function fullStatus(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Diagnóstico completo de ONU obtenido.', $this->smartOltService->getOnuFullStatusInfo($id))->send();
    }

    public function runningConfig(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Configuración activa obtenida.', $this->smartOltService->getOnuRunningConfig($id))->send();
    }

    public function signalGraph(string $id, string $graphType): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Gráfica de señal obtenida.', $this->smartOltService->getOnuSignalGraph($id, $graphType))->send();
    }

    public function trafficGraph(string $id, string $graphType): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Gráfica de tráfico obtenida.', $this->smartOltService->getOnuTrafficGraph($id, $graphType))->send();
    }

    public function macs(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Direcciones MAC obtenidas.', $this->smartOltService->getOnuMacs($id))->send();
    }

    public function routerHosts(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('Hosts conectados obtenidos.', $this->smartOltService->getOnuRouterHosts($id))->send();
    }

    public function searchByMac(SearchMacRequest $request): JsonResponse
    {
        $oltId = $request->validated('olt_id');

        return ApiResponseHelper::returnSuccess(
            'Búsqueda por MAC ejecutada.',
            $this->smartOltService->searchByMac(
                (string) $request->validated('mac_address'),
                $oltId ? (string) $oltId : null
            )
        )->send();
    }

    public function authorizationPresets(Request $request): JsonResponse
    {
        $oltId = $request->query('olt_id');

        return ApiResponseHelper::returnSuccess(
            'Presets de autorización obtenidos.',
            $this->smartOltService->getAuthorizationPresets($oltId ? (string) $oltId : null)
        )->send();
    }

    public function authorize(AuthorizeOnuRequest $request): JsonResponse
    {
        $result = $this->smartOltService->authorizeOnu($request->validated());

        return ApiResponseHelper::created('ONU autorizada correctamente.', $result)->send();
    }

    public function move(string $id, MoveOnuRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'ONU trasladada correctamente.',
            $this->smartOltService->moveOnu($id, $request->validated())
        )->send();
    }

    public function updateSn(string $id, UpdateOnuSnRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Número de serie actualizado.',
            $this->smartOltService->updateOnuSn($id, (string) $request->validated('sn'))
        )->send();
    }

    public function changeType(string $id, ChangeOnuTypeRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Modelo de ONU actualizado.',
            $this->smartOltService->changeOnuType($id, (string) $request->validated('onu_type'))
        )->send();
    }

    public function updateLocation(string $id, UpdateOnuLocationRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Ubicación de ONU actualizada.',
            $this->smartOltService->updateOnuLocation($id, $request->validated())
        )->send();
    }

    public function updateVlan(string $id, UpdateOnuVlanRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'VLAN de ONU actualizada.',
            $this->smartOltService->updateOnuVlan($id, $request->validated())
        )->send();
    }

    public function updateMode(string $id, UpdateOnuModeRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Modo de ONU actualizado.',
            $this->smartOltService->updateOnuMode($id, (string) $request->validated('mode'))
        )->send();
    }

    public function updateSpeedProfiles(string $id, UpdateOnuSpeedProfilesRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Perfiles de velocidad actualizados.',
            $this->smartOltService->updateOnuSpeedProfiles($id, $request->validated())
        )->send();
    }

    public function reboot(string $id): JsonResponse
    {
        $success = $this->smartOltService->rebootOnu($id);

        return $success
            ? ApiResponseHelper::returnSuccess('ONU reiniciada correctamente.')->send()
            : ApiResponseHelper::returnError('Fallo al reiniciar ONU.')->send();
    }

    public function enable(string $id): JsonResponse
    {
        $success = $this->smartOltService->enableOnu($id);

        return $success
            ? ApiResponseHelper::returnSuccess('ONU habilitada correctamente.')->send()
            : ApiResponseHelper::returnError('Fallo al habilitar ONU.')->send();
    }

    public function disable(string $id): JsonResponse
    {
        $success = $this->smartOltService->disableOnu($id);

        return $success
            ? ApiResponseHelper::returnSuccess('ONU deshabilitada correctamente.')->send()
            : ApiResponseHelper::returnError('Fallo al deshabilitar ONU.')->send();
    }

    public function destroy(string $id): JsonResponse
    {
        $success = $this->smartOltService->deleteOnu($id);

        return $success
            ? ApiResponseHelper::destroySuccess('ONU')->send()
            : ApiResponseHelper::returnError('Fallo al eliminar ONU.')->send();
    }

    public function bulkEnable(BulkOnusRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Habilitación masiva ejecutada.',
            $this->smartOltService->bulkEnableOnus($request->validated('onus'))
        )->send();
    }

    public function bulkDisable(BulkOnusRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Suspensión masiva ejecutada.',
            $this->smartOltService->bulkDisableOnus($request->validated('onus'))
        )->send();
    }

    public function restoreDefaults(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Valores de fábrica restaurados.',
            $this->smartOltService->restoreFactoryDefaults($id)
        )->send();
    }

    public function resyncConfig(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Configuración resincronizada.',
            $this->smartOltService->resyncConfig($id)
        )->send();
    }

    public function tickets(string $id, Request $request): JsonResponse
    {
        $limit = max(1, (int) $request->query('limit', 50));
        $offset = max(0, (int) $request->query('offset', 0));

        return ApiResponseHelper::returnSuccess(
            'Tickets de ONU obtenidos.',
            $this->smartOltService->getExternalTickets($id, $limit, $offset)
        )->send();
    }

    public function openTicket(string $id, OpenTicketRequest $request): JsonResponse
    {
        return ApiResponseHelper::created(
            'Ticket creado correctamente.',
            $this->smartOltService->openExternalTicket($id, $request->validated())
        )->send();
    }

    public function updateTicket(string $id, UpdateTicketRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Ticket actualizado correctamente.',
            $this->smartOltService->updateExternalTicket($id, $request->validated())
        )->send();
    }

    public function resolveTicket(string $id, ResolveTicketRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Ticket resuelto correctamente.',
            $this->smartOltService->resolveExternalTicket($id, $request->validated())
        )->send();
    }
}
