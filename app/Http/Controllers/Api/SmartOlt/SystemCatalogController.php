<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponseHelper;
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
        return ApiResponseHelper::returnSuccess(
            'Perfiles de velocidad obtenidos.',
            $this->smartOltService->getSpeedProfiles()
        )->send();
    }

    public function onuTypes(Request $request): JsonResponse
    {
        $ponType = $request->query('pon_type');

        return ApiResponseHelper::returnSuccess(
            'Tipos de ONU obtenidos.',
            $this->smartOltService->getOnuTypes($ponType ? (string) $ponType : null)
        )->send();
    }
}
