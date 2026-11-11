<?php

namespace App\Http\Controllers\Api;

use App\Enums\NetworkStatus;
use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\TrunkCable;
use App\Services\FiberTopologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrunkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TrunkCable::with('zone:id,name,code')->orderBy('name');

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->input('zone_id'));
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $paginator = $query->paginate($perPage);

        return ApiResponseHelper::paginated('Cables troncales obtenidos.', $paginator)->send();
    }

    public function store(Request $request, FiberTopologyService $topologyService): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'code' => 'required|string|max:50|unique:trunk_cables,code',
            'name' => 'required|string|max:100',
            'fiber_count' => 'required|integer|in:12,24,48,96,144',
            'path' => 'nullable|array',
            'length_km' => 'nullable|numeric|min:0',
            'map_color' => 'nullable|string|max:20',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
            'is_visible' => 'nullable|boolean',
        ]);

        $fiberCount = (int) $validated['fiber_count'];
        $bufferCount = $fiberCount > 12 ? (int) ($fiberCount / 12) : 1;
        $strandsPerBuffer = $fiberCount > 12 ? 12 : $fiberCount;

        $cable = TrunkCable::create([
            ...$validated,
            'buffer_count' => $bufferCount,
            'strands_per_buffer' => $strandsPerBuffer,
            'status' => $validated['status'] ?? NetworkStatus::Active,
            'is_visible' => $validated['is_visible'] ?? true,
        ]);

        $topologyService->generateBuffersAndStrands($cable);

        return ApiResponseHelper::storeSuccess('Cable troncal', $cable->load('buffers.strands.color'))->send();
    }

    public function show(int $id): JsonResponse
    {
        $cable = TrunkCable::with(['zone', 'buffers.color', 'buffers.strands.color'])->findOrFail($id);

        return ApiResponseHelper::returnSuccess('Cable troncal obtenido.', $cable)->send();
    }

    public function strands(int $id): JsonResponse
    {
        $cable = TrunkCable::with([
            'buffers.color',
            'buffers.strands.color',
            'buffers.strands.splicesAsIn.closure',
            'buffers.strands.splicesAsIn.napBox',
        ])->findOrFail($id);

        $dto = [
            'cable_id' => $cable->id,
            'code' => $cable->code,
            'name' => $cable->name,
            'fiber_count' => $cable->fiber_count,
            'buffers' => $cable->buffers->map(function ($b) {
                return [
                    'buffer_number' => $b->buffer_number,
                    'color' => $b->color,
                    'strands' => $b->strands->map(function ($s) {
                        return [
                            'strand_number' => $s->strand_number,
                            'global_number' => $s->global_number,
                            'color' => $s->color,
                            'status' => $s->status->value,
                            'splices' => $s->splicesAsIn,
                        ];
                    }),
                ];
            }),
        ];

        return ApiResponseHelper::returnSuccess('Hilos de fibra obtenidos.', $dto)->send();
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cable = TrunkCable::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'zone_id' => 'nullable|exists:zones,id',
            'path' => 'nullable|array',
            'length_km' => 'nullable|numeric|min:0',
            'map_color' => 'nullable|string|max:20',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
            'is_visible' => 'nullable|boolean',
        ]);

        $cable->update($validated);

        return ApiResponseHelper::updateSuccess('Cable troncal', $cable)->send();
    }

    public function destroy(int $id): JsonResponse
    {
        $cable = TrunkCable::findOrFail($id);
        $cable->delete();

        return ApiResponseHelper::destroySuccess('Cable troncal')->send();
    }
}
