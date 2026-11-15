<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemCatalogController extends Controller
{
    public function __construct(
        protected readonly SmartOltService $smartOltService
    ) {}

    public function speedProfiles(): JsonResponse
    {
        return ApiResponse::raw($this->smartOltService->getSpeedProfiles());
    }

    public function onuTypes(Request $request): JsonResponse
    {
        $ponType = $request->query('pon_type');
        return ApiResponse::raw($this->smartOltService->getOnuTypes($ponType ? (string) $ponType : null));
    }
}
