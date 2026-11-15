<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\SmartOlt\OltResource;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OltController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function index(): JsonResponse
    {
        $olts = $this->smartOltService->getOlts();
        return ApiResponse::success(OltResource::collection($olts));
    }

    public function show(string $id): JsonResponse
    {
        $olt = $this->smartOltService->getOltDetails($id);

        if ($olt === null) {
            return ApiResponse::notFound('OLT');
        }

        return ApiResponse::success(new OltResource($olt));
    }

    public function uptimeAndTemperature(?string $id = null): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOltUptimeAndTemperature($id));
    }

    public function cards(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOltCards($id));
    }

    public function ponPorts(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOltPonPorts($id));
    }

    public function outagePons(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOltOutagePons($id));
    }

    public function uplinkPorts(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getOltUplinkPorts($id));
    }

    public function vlans(string $id): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getVlans($id));
    }

    public function addVlan(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'vlan_id' => ['required', 'integer', 'min:1', 'max:4094'],
            'description' => ['nullable', 'string', 'max:100'],
        ]);

        return ApiResponse::raw($this->smartOltService->addVlan($id, $request->all()), 201);
    }

    public function sync(): JsonResponse
    {
        return ApiResponse::success($this->smartOltService->syncToDatabase());
    }
}
