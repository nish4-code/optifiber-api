<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmartOlt\StoreOdbOrCabinetRequest;
use App\Http\Requests\SmartOlt\StoreZoneRequest;
use App\Http\Requests\SmartOlt\UpdateOdbOrCabinetRequest;
use App\Http\Resources\SmartOlt\ZoneResource;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;

class ZoneController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function index(): JsonResponse
    {
        $zones = $this->smartOltService->getZones();

        return ApiResponseHelper::returnSuccess('Zonas de SmartOLT obtenidas.', ZoneResource::collection($zones))->send();
    }

    public function store(StoreZoneRequest $request): JsonResponse
    {
        $zone = $this->smartOltService->addZone($request->validated('name'));

        return ApiResponseHelper::created('Zona SmartOLT creada correctamente.', new ZoneResource($zone))->send();
    }

    public function odbs(string $zoneId): JsonResponse
    {
        return ApiResponseHelper::returnSuccess('ODBs obtenidos.', $this->smartOltService->getOdbs($zoneId))->send();
    }

    public function addOdb(StoreOdbOrCabinetRequest $request): JsonResponse
    {
        return ApiResponseHelper::created(
            'ODB creado correctamente.',
            $this->smartOltService->addOdb($request->validated())
        )->send();
    }

    public function editOdb(string $odbExternalId, UpdateOdbOrCabinetRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'ODB actualizado correctamente.',
            $this->smartOltService->editOdb($odbExternalId, $request->validated())
        )->send();
    }

    public function deleteOdb(string $odbExternalId): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'ODB eliminado correctamente.',
            $this->smartOltService->deleteOdb($odbExternalId)
        )->send();
    }

    public function cabinets(string $zoneId): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Armarios obtenidos.',
            $this->smartOltService->getCabinets($zoneId)
        )->send();
    }

    public function addCabinet(StoreOdbOrCabinetRequest $request): JsonResponse
    {
        return ApiResponseHelper::created(
            'Armario creado correctamente.',
            $this->smartOltService->addCabinet($request->validated())
        )->send();
    }

    public function editCabinet(string $cabinetExternalId, UpdateOdbOrCabinetRequest $request): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Armario actualizado correctamente.',
            $this->smartOltService->editCabinet($cabinetExternalId, $request->validated())
        )->send();
    }

    public function deleteCabinet(string $cabinetExternalId): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Armario eliminado correctamente.',
            $this->smartOltService->deleteCabinet($cabinetExternalId)
        )->send();
    }
}
