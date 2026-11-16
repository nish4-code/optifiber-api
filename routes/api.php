<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClosureController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FiberColorController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NapController;
use App\Http\Controllers\Api\SmartOlt\OltController as SmartOltOltController;
use App\Http\Controllers\Api\SmartOlt\OnuController as SmartOltOnuController;
use App\Http\Controllers\Api\SmartOlt\SystemCatalogController as SmartOltCatalogController;
use App\Http\Controllers\Api\SmartOlt\WebhookController as SmartOltWebhookController;
use App\Http\Controllers\Api\SmartOlt\ZoneController as SmartOltZoneController;
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
Route::post('smartolt/webhook', [SmartOltWebhookController::class, 'handle']);

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
        // Endpoints consumidos por optifiber-frontend (formato { mo, dto })
        Route::get('onus', [SmartOltController::class, 'onus']);
        Route::get('onus/{externalId}/signal', [SmartOltController::class, 'signal']);
        Route::post('sync', [SmartOltController::class, 'sync']);

        // OLTs y Hardware
        Route::prefix('olts')->group(function () {
            Route::get('/', [SmartOltOltController::class, 'index']);
            Route::get('uptime-temperature', [SmartOltOltController::class, 'uptimeAndTemperature']);
            Route::get('{id}', [SmartOltOltController::class, 'show']);
            Route::get('{id}/uptime-temperature', [SmartOltOltController::class, 'uptimeAndTemperature']);
            Route::get('{id}/cards', [SmartOltOltController::class, 'cards']);
            Route::get('{id}/pon-ports', [SmartOltOltController::class, 'ponPorts']);
            Route::get('{id}/outage-pons', [SmartOltOltController::class, 'outagePons']);
            Route::get('{id}/uplink-ports', [SmartOltOltController::class, 'uplinkPorts']);
            Route::get('{id}/vlans', [SmartOltOltController::class, 'vlans']);
            Route::post('{id}/vlans', [SmartOltOltController::class, 'addVlan']);
        });

        // Catálogos
        Route::get('speed-profiles', [SmartOltCatalogController::class, 'speedProfiles']);
        Route::get('onu-types', [SmartOltCatalogController::class, 'onuTypes']);

        // Zonas, ODBs y Armarios SmartOLT
        Route::prefix('zones')->group(function () {
            Route::get('/', [SmartOltZoneController::class, 'index']);
            Route::post('/', [SmartOltZoneController::class, 'store']);
            Route::get('{zoneId}/odbs', [SmartOltZoneController::class, 'odbs']);
            Route::get('{zoneId}/cabinets', [SmartOltZoneController::class, 'cabinets']);
        });

        Route::prefix('odbs')->group(function () {
            Route::post('/', [SmartOltZoneController::class, 'addOdb']);
            Route::post('{odbId}/edit', [SmartOltZoneController::class, 'editOdb']);
            Route::delete('{odbId}', [SmartOltZoneController::class, 'deleteOdb']);
        });

        Route::prefix('cabinets')->group(function () {
            Route::post('/', [SmartOltZoneController::class, 'addCabinet']);
            Route::post('{cabinetId}/edit', [SmartOltZoneController::class, 'editCabinet']);
            Route::delete('{cabinetId}', [SmartOltZoneController::class, 'deleteCabinet']);
        });

        // Gestión avanzada, trazado y control remoto de ONUs
        Route::prefix('onus')->group(function () {
            Route::get('unconfigured', [SmartOltOnuController::class, 'unconfigured']);
            Route::get('map', [SmartOltOnuController::class, 'mapPoints']);
            Route::get('search-mac', [SmartOltOnuController::class, 'searchByMac']);
            Route::get('presets', [SmartOltOnuController::class, 'authorizationPresets']);
            Route::get('sn/{sn}', [SmartOltOnuController::class, 'showBySn']);
            Route::get('{id}', [SmartOltOnuController::class, 'show']);
            Route::get('{id}/trace', [SmartOltOnuController::class, 'trace']);
            Route::get('{id}/status', [SmartOltOnuController::class, 'status']);
            Route::get('{id}/status-history', [SmartOltOnuController::class, 'statusHistory']);
            Route::get('{id}/full-status', [SmartOltOnuController::class, 'fullStatus']);
            Route::get('{id}/running-config', [SmartOltOnuController::class, 'runningConfig']);
            Route::get('{id}/signal-graph/{graphType}', [SmartOltOnuController::class, 'signalGraph']);
            Route::get('{id}/traffic-graph/{graphType}', [SmartOltOnuController::class, 'trafficGraph']);
            Route::get('{id}/macs', [SmartOltOnuController::class, 'macs']);
            Route::get('{id}/router-hosts', [SmartOltOnuController::class, 'routerHosts']);

            Route::post('authorize', [SmartOltOnuController::class, 'authorize']);
            Route::post('bulk-enable', [SmartOltOnuController::class, 'bulkEnable']);
            Route::post('bulk-disable', [SmartOltOnuController::class, 'bulkDisable']);

            Route::post('{id}/move', [SmartOltOnuController::class, 'move']);
            Route::post('{id}/update-sn', [SmartOltOnuController::class, 'updateSn']);
            Route::post('{id}/change-type', [SmartOltOnuController::class, 'changeType']);
            Route::post('{id}/update-location', [SmartOltOnuController::class, 'updateLocation']);
            Route::post('{id}/update-vlan', [SmartOltOnuController::class, 'updateVlan']);
            Route::post('{id}/update-mode', [SmartOltOnuController::class, 'updateMode']);
            Route::post('{id}/update-speed-profiles', [SmartOltOnuController::class, 'updateSpeedProfiles']);
            Route::post('{id}/reboot', [SmartOltOnuController::class, 'reboot']);
            Route::post('{id}/enable', [SmartOltOnuController::class, 'enable']);
            Route::post('{id}/disable', [SmartOltOnuController::class, 'disable']);
            Route::delete('{id}', [SmartOltOnuController::class, 'destroy']);
            Route::post('{id}/restore-defaults', [SmartOltOnuController::class, 'restoreDefaults']);
            Route::post('{id}/resync-config', [SmartOltOnuController::class, 'resyncConfig']);

            Route::get('{id}/tickets', [SmartOltOnuController::class, 'tickets']);
            Route::post('{id}/tickets', [SmartOltOnuController::class, 'openTicket']);
            Route::post('{id}/tickets/update', [SmartOltOnuController::class, 'updateTicket']);
            Route::post('{id}/tickets/resolve', [SmartOltOnuController::class, 'resolveTicket']);
        });
    });
});
