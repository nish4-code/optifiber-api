<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Zone::withCount(['trunkCables', 'napBoxes', 'spliceClosures', 'customers'])->get());
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

        return response()->json($zone, 201);
    }

    public function show(int $id): JsonResponse
    {
        $zone = Zone::with(['trunkCables', 'napBoxes', 'spliceClosures'])->findOrFail($id);

        return response()->json($zone);
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

        return response()->json($zone);
    }

    public function destroy(int $id): JsonResponse
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return response()->json(['message' => 'Zona eliminada correctamente.']);
    }
}
