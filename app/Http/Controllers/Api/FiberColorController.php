<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\FiberColor;
use Illuminate\Http\JsonResponse;

class FiberColorController extends Controller
{
    public function index(): JsonResponse
    {
        $colors = FiberColor::orderBy('position')->get();

        return ApiResponseHelper::returnSuccess('Colores TIA-598 obtenidos correctamente.', $colors)->send();
    }
}
