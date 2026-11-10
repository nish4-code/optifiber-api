<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmartOltOnu;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartOltController extends Controller
{
    public function onus(Request $request): JsonResponse
    {
        $query = SmartOltOnu::with(['olt', 'customer'])->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sn', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%")
                    ->orWhere('zone', 'ilike', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function signal(string $externalId, SmartOltService $service): JsonResponse
    {
        $signal = $service->getOnuSignal($externalId);

        if (! $signal) {
            return response()->json(['message' => 'No se pudo obtener la señal de la ONU.'], 404);
        }

        return response()->json($signal);
    }

    public function sync(SmartOltService $service): JsonResponse
    {
        $result = $service->syncToDatabase();

        return response()->json([
            'message' => 'Sincronización con SmartOLT ejecutada exitosamente.',
            'summary' => $result,
        ]);
    }
}
