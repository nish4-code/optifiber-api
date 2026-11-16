<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SmartOlt\StoreVlanRequest;
use App\Http\Resources\SmartOlt\OltResource;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;

class OltController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function index(): JsonResponse
    {
        $olts = $this->smartOltService->getOlts();

        return ApiResponseHelper::returnSuccess('OLTs obtenidas.', OltResource::collection($olts))->send();
    }

    public function show(string $id): JsonResponse
    {
        $olt = $this->smartOltService->getOltDetails($id);

        if ($olt === null) {
            return ApiResponseHelper::notFound('OLT')->send();
        }

        return ApiResponseHelper::returnSuccess('Detalle de OLT obtenido.', new OltResource($olt))->send();
    }

    public function uptimeAndTemperature(?string $id = null): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Telemetría de OLT obtenida.',
            $this->smartOltService->getOltUptimeAndTemperature($id)
        )->send();
    }

    public function cards(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Tarjetas de OLT obtenidas.',
            $this->smartOltService->getOltCards($id)
        )->send();
    }

    public function ponPorts(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Puertos PON obtenidos.',
            $this->smartOltService->getOltPonPorts($id)
        )->send();
    }

    public function outagePons(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Puertos PON en corte obtenidos.',
            $this->smartOltService->getOltOutagePons($id)
        )->send();
    }

    public function uplinkPorts(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'Puertos Uplink obtenidos.',
            $this->smartOltService->getOltUplinkPorts($id)
        )->send();
    }

    public function vlans(string $id): JsonResponse
    {
        return ApiResponseHelper::returnSuccess(
            'VLANs de OLT obtenidas.',
            $this->smartOltService->getVlans($id)
        )->send();
    }

    public function addVlan(string $id, StoreVlanRequest $request): JsonResponse
    {
        return ApiResponseHelper::created(
            'VLAN creada correctamente.',
            $this->smartOltService->addVlan($id, $request->validated())
        )->send();
    }
}
