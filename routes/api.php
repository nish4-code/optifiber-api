<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClosureController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FiberColorController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NapController;
use App\Http\Controllers\Api\SmartOltController;
use App\Http\Controllers\Api\TrunkController;
use App\Http\Controllers\Api\ZoneController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::get('fiber-colors', [FiberColorController::class, 'index']);

// Rutas Protegidas (Requieren Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth Profile
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Dashboard Telemetría y KPIs
    Route::prefix('dashboard')->group(function () {
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('zones-capacity', [DashboardController::class, 'zonesCapacity']);
        Route::get('latest-customers', [DashboardController::class, 'latestCustomers']);
    });

    // Alertas de Red
    Route::get('alerts', [AlertController::class, 'index']);
    Route::patch('alerts/{id}/resolve', [AlertController::class, 'resolve']);

    // Visor GIS y Trazado Óptico
    Route::prefix('map')->group(function () {
        Route::get('elements', [MapController::class, 'elements']);
        Route::get('trace/{strandId}', [MapController::class, 'trace']);
    });

    // Zonas de Red
    Route::apiResource('zones', ZoneController::class);

    // Troncales Principales
    Route::apiResource('trunks', TrunkController::class);
    Route::get('trunks/{id}/strands', [TrunkController::class, 'strands']);

    // Cajas NAP
    Route::apiResource('naps', NapController::class);
    Route::get('naps/{id}/ports', [NapController::class, 'ports']);

    // Mufas y Matriz de Fusión
    Route::apiResource('closures', ClosureController::class);
    Route::get('closures/{id}/splices', [ClosureController::class, 'splices']);
    Route::post('closures/{id}/splices', [ClosureController::class, 'storeSplice']);
    Route::delete('closures/{closureId}/splices/{spliceId}', [ClosureController::class, 'deleteSplice']);

    // Clientes y Abonados
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{id}/events', [CustomerController::class, 'events']);

    // Integración SmartOLT
    Route::prefix('smartolt')->group(function () {
        Route::get('onus', [SmartOltController::class, 'onus']);
        Route::get('onus/{externalId}/signal', [SmartOltController::class, 'signal']);
        Route::post('sync', [SmartOltController::class, 'sync']);
    });
});
