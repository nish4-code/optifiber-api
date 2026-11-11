<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponseHelper;
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

        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginator = $query->paginate($perPage);

        return ApiResponseHelper::paginated('ONUs de SmartOLT obtenidas.', $paginator)->send();
    }

    public function signal(string $externalId, SmartOltService $service): JsonResponse
    {
        $signal = $service->getOnuSignal($externalId);

        if (! $signal) {
            return ApiResponseHelper::notFound('Señal de ONU')->send();
        }

        return ApiResponseHelper::returnSuccess('Señal óptica de ONU obtenida.', $signal)->send();
    }

    public function sync(SmartOltService $service): JsonResponse
    {
        $result = $service->syncToDatabase();

        return ApiResponseHelper::returnSuccess('Sincronización con SmartOLT ejecutada exitosamente.', $result)->send();
    }
}
