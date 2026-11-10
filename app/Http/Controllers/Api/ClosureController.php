<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClosureType;
use App\Enums\MountType;
use App\Enums\NetworkStatus;
use App\Http\Controllers\Controller;
use App\Models\FiberSplice;
use App\Models\SpliceClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClosureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SpliceClosure::with('zone:id,name,code')->withCount('splices')->orderBy('code');

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->input('zone_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'code' => 'required|string|max:50|unique:splice_closures,code',
            'name' => 'required|string|max:100',
            'type' => ['nullable', Rule::enum(ClosureType::class)],
            'mount_type' => ['nullable', Rule::enum(MountType::class)],
            'fiber_capacity' => 'nullable|integer|in:24,48,96,144',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'reserve_meters' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
        ]);

        $closure = SpliceClosure::create([
            ...$validated,
            'type' => $validated['type'] ?? ClosureType::Splice,
            'mount_type' => $validated['mount_type'] ?? MountType::Pole,
            'fiber_capacity' => $validated['fiber_capacity'] ?? 144,
            'status' => $validated['status'] ?? NetworkStatus::Active,
        ]);

        return response()->json($closure, 201);
    }

    public function show(int $id): JsonResponse
    {
        $closure = SpliceClosure::with('zone')->findOrFail($id);

        return response()->json($closure);
    }

    public function splices(int $id): JsonResponse
    {
        $closure = SpliceClosure::with([
            'splices.strandIn.buffer.color',
            'splices.strandIn.color',
            'splices.strandIn.trunkCable',
            'splices.strandOut.buffer.color',
            'splices.strandOut.color',
            'splices.strandOut.trunkCable',
            'splices.napBox',
        ])->findOrFail($id);

        $fusedCount = $closure->splices->count();
        $totalCapacity = $closure->fiber_capacity;
        $availableCount = max(0, $totalCapacity - $fusedCount);
        $avgLoss = $closure->splices->avg('loss_db');

        return response()->json([
            'closure' => [
                'id' => $closure->id,
                'code' => $closure->code,
                'name' => $closure->name,
                'type' => $closure->type->value,
                'mount_type' => $closure->mount_type->value,
                'status' => $closure->status->value,
                'reserve_meters' => $closure->reserve_meters,
            ],
            'telemetry' => [
                'total_fibers' => $totalCapacity,
                'fused_fibers' => $fusedCount,
                'available_fibers' => $availableCount,
                'average_loss_db' => $avgLoss ? round($avgLoss, 3) : 0.05,
                'buffers_config' => '12 Tubos Buffer × 12 Hilos Sílice',
            ],
            'splices' => $closure->splices->map(function ($s) {
                return [
                    'id' => $s->id,
                    'tray_number' => $s->tray_number,
                    'strand_in' => [
                        'id' => $s->strandIn->id,
                        'cable' => $s->strandIn->trunkCable->name,
                        'buffer' => $s->strandIn->buffer->buffer_number,
                        'buffer_color' => $s->strandIn->buffer->color->name,
                        'strand_number' => $s->strandIn->strand_number,
                        'global_number' => $s->strandIn->global_number,
                        'strand_color' => $s->strandIn->color->name,
                        'color_hex' => $s->strandIn->color->hex,
                    ],
                    'strand_out' => $s->strandOut ? [
                        'id' => $s->strandOut->id,
                        'cable' => $s->strandOut->trunkCable->name,
                        'buffer' => $s->strandOut->buffer->buffer_number,
                        'buffer_color' => $s->strandOut->buffer->color->name,
                        'strand_number' => $s->strandOut->strand_number,
                        'global_number' => $s->strandOut->global_number,
                        'strand_color' => $s->strandOut->color->name,
                        'color_hex' => $s->strandOut->color->hex,
                    ] : null,
                    'nap_box' => $s->napBox ? [
                        'id' => $s->napBox->id,
                        'code' => $s->napBox->code,
                        'name' => $s->napBox->name,
                    ] : null,
                    'loss_db' => $s->loss_db,
                    'status' => $s->status->value,
                ];
            }),
        ]);
    }

    public function storeSplice(Request $request, int $id): JsonResponse
    {
        $closure = SpliceClosure::findOrFail($id);

        $validated = $request->validate([
            'tray_number' => 'nullable|integer|min:1',
            'strand_in_id' => 'required|exists:fiber_strands,id',
            'strand_out_id' => 'nullable|exists:fiber_strands,id|different:strand_in_id',
            'nap_box_id' => 'nullable|exists:nap_boxes,id',
            'loss_db' => 'nullable|numeric|min:0|max:5',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
        ]);

        $splice = FiberSplice::updateOrCreate(
            [
                'splice_closure_id' => $closure->id,
                'strand_in_id' => $validated['strand_in_id'],
            ],
            [
                'tray_number' => $validated['tray_number'] ?? 1,
                'strand_out_id' => $validated['strand_out_id'] ?? null,
                'nap_box_id' => $validated['nap_box_id'] ?? null,
                'loss_db' => $validated['loss_db'] ?? 0.05,
                'status' => $validated['status'] ?? NetworkStatus::Active,
            ]
        );

        return response()->json($splice, 201);
    }

    public function deleteSplice(int $closureId, int $spliceId): JsonResponse
    {
        $splice = FiberSplice::where('splice_closure_id', $closureId)->where('id', $spliceId)->firstOrFail();
        $splice->delete();

        return response()->json(['message' => 'Fusión eliminada correctamente.']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $closure = SpliceClosure::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'zone_id' => 'nullable|exists:zones,id',
            'type' => ['nullable', Rule::enum(ClosureType::class)],
            'mount_type' => ['nullable', Rule::enum(MountType::class)],
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'reserve_meters' => 'nullable|numeric|min:0',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
        ]);

        $closure->update($validated);

        return response()->json($closure);
    }

    public function destroy(int $id): JsonResponse
    {
        $closure = SpliceClosure::findOrFail($id);
        $closure->delete();

        return response()->json(['message' => 'Mufa eliminada correctamente.']);
    }
}
