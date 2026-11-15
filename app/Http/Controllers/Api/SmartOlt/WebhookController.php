<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SmartOlt;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SmartOltEvent;
use App\Models\SmartOltOnu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventType = (string) ($payload['event'] ?? 'unknown');
        $onuSn = isset($payload['sn']) ? (string) $payload['sn'] : null;
        $oltId = isset($payload['olt_id']) ? (string) $payload['olt_id'] : null;

        SmartOltEvent::create([
            'event_type' => $eventType,
            'onu_sn' => $onuSn,
            'olt_id' => $oltId,
            'severity' => str_contains($eventType, 'outage') ? 'critical' : 'info',
            'payload' => $payload,
            'occurred_at' => now(),
        ]);

        if ($onuSn && isset($payload['status'])) {
            SmartOltOnu::where('sn', $onuSn)->update([
                'status' => (string) $payload['status'],
                'last_synced_at' => now(),
            ]);
        }

        return ApiResponse::success('Evento procesado correctamente');
    }
}
