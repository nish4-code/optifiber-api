<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = Zone::withCount(['trunkCables', 'napBoxes', 'spliceClosures', 'customers'])->get();

        return ApiResponseHelper::returnSuccess('Zonas obtenidas correctamente.', $zones)->send();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:zones,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'center_lat' => 'nullable|numeric',
            'center_lng' => 'nullable|numeric',
        ]);

        $zone = Zone::create($validated);

        return ApiResponseHelper::storeSuccess('Zona', $zone)->send();
    }

    public function show(int $id): JsonResponse
    {
        $zone = Zone::with(['trunkCables', 'napBoxes', 'spliceClosures'])->findOrFail($id);

        return ApiResponseHelper::returnSuccess('Zona obtenida correctamente.', $zone)->send();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $zone = Zone::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'center_lat' => 'nullable|numeric',
            'center_lng' => 'nullable|numeric',
        ]);

        $zone->update($validated);

        return ApiResponseHelper::updateSuccess('Zona', $zone)->send();
    }

    public function destroy(int $id): JsonResponse
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return ApiResponseHelper::destroySuccess('Zona')->send();
    }
}
