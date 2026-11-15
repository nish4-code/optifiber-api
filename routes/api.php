<?php

declare(strict_types=1);

use App\Http\Controllers\Api\SmartOlt\OltController;
use App\Http\Controllers\Api\SmartOlt\OnuController;
use App\Http\Controllers\Api\SmartOlt\SystemCatalogController;
use App\Http\Controllers\Api\SmartOlt\WebhookController;
use App\Http\Controllers\Api\SmartOlt\ZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('smartolt')->group(function () {
    Route::post('webhook', [WebhookController::class, 'handle']);

    Route::prefix('olts')->group(function () {
        Route::get('/', [OltController::class, 'index']);
        Route::get('uptime-temperature', [OltController::class, 'uptimeAndTemperature']);
        Route::get('{id}', [OltController::class, 'show']);
        Route::get('{id}/uptime-temperature', [OltController::class, 'uptimeAndTemperature']);
        Route::get('{id}/cards', [OltController::class, 'cards']);
        Route::get('{id}/pon-ports', [OltController::class, 'ponPorts']);
        Route::get('{id}/outage-pons', [OltController::class, 'outagePons']);
        Route::get('{id}/uplink-ports', [OltController::class, 'uplinkPorts']);
        Route::get('{id}/vlans', [OltController::class, 'vlans']);
        Route::post('{id}/vlans', [OltController::class, 'addVlan']);
    });

    Route::post('sync', [OltController::class, 'sync']);

    Route::get('speed-profiles', [SystemCatalogController::class, 'speedProfiles']);
    Route::get('onu-types', [SystemCatalogController::class, 'onuTypes']);

    Route::prefix('zones')->group(function () {
        Route::get('/', [ZoneController::class, 'index']);
        Route::post('/', [ZoneController::class, 'store']);
        Route::get('{zoneId}/odbs', [ZoneController::class, 'odbs']);
        Route::get('{zoneId}/cabinets', [ZoneController::class, 'cabinets']);
    });

    Route::prefix('odbs')->group(function () {
        Route::post('/', [ZoneController::class, 'addOdb']);
        Route::post('{odbId}/edit', [ZoneController::class, 'editOdb']);
        Route::delete('{odbId}', [ZoneController::class, 'deleteOdb']);
    });

    Route::prefix('cabinets')->group(function () {
        Route::post('/', [ZoneController::class, 'addCabinet']);
        Route::post('{cabinetId}/edit', [ZoneController::class, 'editCabinet']);
        Route::delete('{cabinetId}', [ZoneController::class, 'deleteCabinet']);
    });

    Route::prefix('onus')->group(function () {
        Route::get('/', [OnuController::class, 'index']);
        Route::get('unconfigured', [OnuController::class, 'unconfigured']);
        Route::get('map', [OnuController::class, 'mapPoints']);
        Route::get('search-mac', [OnuController::class, 'searchByMac']);
        Route::get('presets', [OnuController::class, 'authorizationPresets']);
        Route::get('sn/{sn}', [OnuController::class, 'showBySn']);
        Route::get('{id}', [OnuController::class, 'show']);
        Route::get('{id}/trace', [OnuController::class, 'trace']);
        Route::get('{id}/signal', [OnuController::class, 'signal']);
        Route::get('{id}/status', [OnuController::class, 'status']);
        Route::get('{id}/status-history', [OnuController::class, 'statusHistory']);
        Route::get('{id}/full-status', [OnuController::class, 'fullStatus']);
        Route::get('{id}/running-config', [OnuController::class, 'runningConfig']);
        Route::get('{id}/signal-graph/{graphType}', [OnuController::class, 'signalGraph']);
        Route::get('{id}/traffic-graph/{graphType}', [OnuController::class, 'trafficGraph']);
        Route::get('{id}/macs', [OnuController::class, 'macs']);
        Route::get('{id}/router-hosts', [OnuController::class, 'routerHosts']);

        Route::post('authorize', [OnuController::class, 'authorize']);
        Route::post('bulk-enable', [OnuController::class, 'bulkEnable']);
        Route::post('bulk-disable', [OnuController::class, 'bulkDisable']);

        Route::post('{id}/move', [OnuController::class, 'move']);
        Route::post('{id}/update-sn', [OnuController::class, 'updateSn']);
        Route::post('{id}/change-type', [OnuController::class, 'changeType']);
        Route::post('{id}/update-location', [OnuController::class, 'updateLocation']);
        Route::post('{id}/update-vlan', [OnuController::class, 'updateVlan']);
        Route::post('{id}/update-mode', [OnuController::class, 'updateMode']);
        Route::post('{id}/update-speed-profiles', [OnuController::class, 'updateSpeedProfiles']);
        Route::post('{id}/reboot', [OnuController::class, 'reboot']);
        Route::post('{id}/enable', [OnuController::class, 'enable']);
        Route::post('{id}/disable', [OnuController::class, 'disable']);
        Route::delete('{id}', [OnuController::class, 'destroy']);
        Route::post('{id}/restore-defaults', [OnuController::class, 'restoreDefaults']);
        Route::post('{id}/resync-config', [OnuController::class, 'resyncConfig']);

        Route::get('{id}/tickets', [OnuController::class, 'tickets']);
        Route::post('{id}/tickets', [OnuController::class, 'openTicket']);
        Route::post('{id}/tickets/update', [OnuController::class, 'updateTicket']);
        Route::post('{id}/tickets/resolve', [OnuController::class, 'resolveTicket']);
    });
});
