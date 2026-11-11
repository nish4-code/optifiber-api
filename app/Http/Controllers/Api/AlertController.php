<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\NetworkAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NetworkAlert::with('alertable')->orderByDesc('created_at');

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $perPage = min(max($request->integer('per_page', 15), 1), 100);
        $alerts = $query->paginate($perPage);

        return ApiResponseHelper::paginated('Alertas de red obtenidas.', $alerts)->send();
    }

    public function resolve(int $id): JsonResponse
    {
        $alert = NetworkAlert::findOrFail($id);
        $alert->update(['resolved_at' => now()]);

        return ApiResponseHelper::returnSuccess('Alerta marcada como resuelta.', $alert)->send();
    }
}
