<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\FiberStrand;
use App\Models\NapBox;
use App\Models\SpliceClosure;
use App\Models\TrunkCable;
use App\Services\FiberTopologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function elements(Request $request): JsonResponse
    {
        $layers = explode(',', $request->query('layers', 'trunks,naps,closures,customers'));

        $data = [];

        if (in_array('trunks', $layers)) {
            $data['trunks'] = TrunkCable::visible()
                ->select(['id', 'code', 'name', 'fiber_count', 'path', 'length_km', 'map_color', 'status'])
                ->get();
        }

        if (in_array('naps', $layers)) {
            $data['naps'] = NapBox::with(['ports', 'feederStrand.color', 'feederStrand.buffer.color'])
                ->select(['id', 'code', 'name', 'splitter_ratio', 'port_capacity', 'address', 'latitude', 'longitude', 'feeder_strand_id', 'map_color', 'map_icon', 'status'])
                ->get()
                ->map(function ($nap) {
                    return [
                        'id' => $nap->id,
                        'code' => $nap->code,
                        'name' => $nap->name,
                        'splitter_ratio' => $nap->splitter_ratio->value,
                        'port_capacity' => $nap->port_capacity,
                        'occupied_ports' => $nap->occupied_ports_count,
                        'free_ports' => $nap->free_ports_count,
                        'occupancy_percentage' => $nap->occupancy_percentage,
                        'address' => $nap->address,
                        'latitude' => $nap->latitude,
                        'longitude' => $nap->longitude,
                        'feeder_strand' => $nap->feederStrand ? [
                            'global_number' => $nap->feederStrand->global_number,
                            'buffer_color' => $nap->feederStrand->buffer?->color?->name,
                            'strand_color' => $nap->feederStrand->color?->name,
                        ] : null,
                        'map_color' => $nap->map_color,
                        'map_icon' => $nap->map_icon,
                        'status' => $nap->status->value,
                    ];
                });
        }

        if (in_array('closures', $layers)) {
            $data['closures'] = SpliceClosure::select(['id', 'code', 'name', 'type', 'mount_type', 'fiber_capacity', 'latitude', 'longitude', 'reserve_meters', 'status'])
                ->get();
        }

        if (in_array('customers', $layers)) {
            $data['customers'] = Customer::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select(['id', 'code', 'full_name', 'phone', 'latitude', 'longitude', 'plan', 'nap_port_id', 'status'])
                ->with('port.napBox:id,code')
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'code' => $c->code,
                        'name' => $c->full_name,
                        'phone' => $c->phone,
                        'latitude' => $c->latitude,
                        'longitude' => $c->longitude,
                        'plan' => $c->plan,
                        'nap_code' => $c->port?->napBox?->code,
                        'port_number' => $c->port?->port_number,
                        'status' => $c->status->value,
                    ];
                });
        }

        return ApiResponseHelper::returnSuccess('Elementos cartográficos obtenidos.', $data)->send();
    }

    public function trace(int $strandId, FiberTopologyService $service): JsonResponse
    {
        $strand = FiberStrand::findOrFail($strandId);
        $result = $service->traceOpticalPath($strand);

        return ApiResponseHelper::returnSuccess('Traza óptica calculada.', $result)->send();
    }
}
