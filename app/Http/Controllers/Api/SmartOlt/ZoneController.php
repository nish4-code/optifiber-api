<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmartOlt\StoreZoneRequest;
use App\Http\Resources\SmartOlt\ZoneResource;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function index(): JsonResponse
    {
        $zones = $this->smartOltService->getZones();
        return ApiResponse::success(ZoneResource::collection($zones));
    }

    public function store(StoreZoneRequest $request): JsonResponse
    {
        $zone = $this->smartOltService->addZone($request->validated('name'));
        return ApiResponse::created(new ZoneResource($zone));
    }

    public function odbs(string $zoneId): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOdbs($zoneId));
    }

    public function addOdb(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'zone_id' => ['required', 'string'],
        ]);

        return ApiResponse::raw($this->smartOltService->addOdb($request->all()), 201);
    }

    public function editOdb(string $odbExternalId, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->editOdb($odbExternalId, $request->all()));
    }

    public function deleteOdb(string $odbExternalId): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->deleteOdb($odbExternalId));
    }

    public function cabinets(string $zoneId): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getCabinets($zoneId));
    }

    public function addCabinet(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'zone_id' => ['required', 'string'],
        ]);

        return ApiResponse::raw($this->smartOltService->addCabinet($request->all()), 201);
    }

    public function editCabinet(string $cabinetExternalId, Request $request): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->editCabinet($cabinetExternalId, $request->all()));
    }

    public function deleteCabinet(string $cabinetExternalId): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->deleteCabinet($cabinetExternalId));
    }
}
