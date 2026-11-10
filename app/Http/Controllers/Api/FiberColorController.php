<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FiberColor;
use Illuminate\Http\JsonResponse;

class FiberColorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(FiberColor::orderBy('position')->get());
    }
}
