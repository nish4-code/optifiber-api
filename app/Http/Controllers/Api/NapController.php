<?php

namespace App\Http\Controllers\Api;

use App\Enums\NetworkStatus;
use App\Enums\SplitterRatio;
use App\Http\Controllers\Controller;
use App\Models\NapBox;
use App\Services\FiberTopologyService;
use App\Services\NetworkAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NapBox::with(['zone:id,name,code', 'ports', 'feederStrand.color'])->orderBy('code');

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->input('zone_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        $naps = $query->paginate(15)->through(function ($nap) {
            return [
                'id' => $nap->id,
                'code' => $nap->code,
                'name' => $nap->name,
                'zone' => $nap->zone,
                'splitter_ratio' => $nap->splitter_ratio->value,
                'port_capacity' => $nap->port_capacity,
                'occupied_ports' => $nap->occupied_ports_count,
                'free_ports' => $nap->free_ports_count,
                'occupancy_percentage' => $nap->occupancy_percentage,
                'address' => $nap->address,
                'latitude' => $nap->latitude,
                'longitude' => $nap->longitude,
                'map_color' => $nap->map_color,
                'map_icon' => $nap->map_icon,
                'status' => $nap->status->value,
                'notes' => $nap->notes,
                'created_at' => $nap->created_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json($naps);
    }

    public function store(Request $request, FiberTopologyService $topologyService, NetworkAlertService $alertService): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'code' => 'required|string|max:50|unique:nap_boxes,code',
            'name' => 'required|string|max:100',
            'splitter_ratio' => ['required', Rule::enum(SplitterRatio::class)],
            'address' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'feeder_strand_id' => 'nullable|exists:fiber_strands,id',
            'map_color' => 'nullable|string|max:20',
            'map_icon' => 'nullable|string|max:30',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
            'notes' => 'nullable|string',
        ]);

        $capacity = $validated['splitter_ratio'] === SplitterRatio::Ratio1x16->value ? 16 : 8;

        $nap = NapBox::create([
            ...$validated,
            'port_capacity' => $capacity,
            'status' => $validated['status'] ?? NetworkStatus::Active,
        ]);

        $topologyService->generateNapPorts($nap);
        $alertService->evaluateNapCapacity($nap);

        return response()->json($nap->load('ports'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $nap = NapBox::with(['zone', 'ports.customer', 'feederStrand.color', 'feederStrand.buffer.color'])->findOrFail($id);

        return response()->json([
            'id' => $nap->id,
            'code' => $nap->code,
            'name' => $nap->name,
            'zone' => $nap->zone,
            'splitter_ratio' => $nap->splitter_ratio->value,
            'port_capacity' => $nap->port_capacity,
            'occupied_ports' => $nap->occupied_ports_count,
            'free_ports' => $nap->free_ports_count,
            'occupancy_percentage' => $nap->occupancy_percentage,
            'address' => $nap->address,
            'latitude' => $nap->latitude,
            'longitude' => $nap->longitude,
            'feeder_strand' => $nap->feederStrand,
            'map_color' => $nap->map_color,
            'map_icon' => $nap->map_icon,
            'status' => $nap->status->value,
            'notes' => $nap->notes,
            'ports' => $nap->ports->map(function ($p) {
                return [
                    'id' => $p->id,
                    'port_number' => $p->port_number,
                    'status' => $p->status->value,
                    'customer' => $p->customer ? [
                        'id' => $p->customer->id,
                        'code' => $p->customer->code,
                        'name' => $p->customer->full_name,
                        'phone' => $p->customer->phone,
                        'plan' => $p->customer->plan,
                    ] : null,
                ];
            }),
        ]);
    }

    public function ports(int $id): JsonResponse
    {
        $nap = NapBox::findOrFail($id);
        $ports = $nap->ports()->with('customer')->orderBy('port_number')->get();

        return response()->json([
            'nap_code' => $nap->code,
            'port_capacity' => $nap->port_capacity,
            'ports' => $ports,
        ]);
    }

    public function update(Request $request, int $id, NetworkAlertService $alertService): JsonResponse
    {
        $nap = NapBox::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'zone_id' => 'nullable|exists:zones,id',
            'address' => 'nullable|string|max:255',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'feeder_strand_id' => 'nullable|exists:fiber_strands,id',
            'map_color' => 'nullable|string|max:20',
            'map_icon' => 'nullable|string|max:30',
            'status' => ['nullable', Rule::enum(NetworkStatus::class)],
            'notes' => 'nullable|string',
        ]);

        $nap->update($validated);
        $alertService->evaluateNapCapacity($nap);

        return response()->json($nap);
    }

    public function destroy(int $id): JsonResponse
    {
        $nap = NapBox::findOrFail($id);
        $nap->delete();

        return response()->json(['message' => 'Caja NAP eliminada correctamente.']);
    }
}
