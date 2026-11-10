<?php

namespace App\Services\SmartOlt;

class SmartOltMockClient
{
    public function getOlts(): array
    {
        return [
            [
                'id' => 'olt_kishuara_01',
                'name' => 'OLT Principal Kishuara (Chiclayo)',
                'ip' => '192.168.100.10',
                'hardware_version' => 'Huawei SmartAX MA5800-X7',
                'status' => 'online',
                'metrics' => [
                    'cpu_usage' => 18,
                    'memory_usage' => 34,
                    'temperature' => 41.5,
                    'pon_ports_total' => 16,
                    'pon_ports_active' => 8,
                    'total_bandwidth_gbps' => 9.84,
                ],
            ],
        ];
    }

    public function getOnus(): array
    {
        $onus = [];
        $firstNames = ['Juan', 'María', 'Carlos', 'Ana', 'Jorge', 'Rosa', 'Luis', 'Carmen', 'Pedro', 'Lucía', 'Pool', 'Jerzy'];
        $lastNames = ['Pérez', 'García', 'López', 'Torres', 'Ramírez', 'Flores', 'Vargas', 'Quispe', 'Mendoza', 'Llacta', 'León'];

        for ($i = 1; $i <= 35; $i++) {
            $num = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $fn = $firstNames[($i * 3) % count($firstNames)];
            $ln = $lastNames[($i * 7) % count($lastNames)];

            $isCritical = ($i === 4 || $i === 17);
            $isOffline = ($i === 12);

            $status = $isOffline ? 'LOS' : 'Online';
            $rx = $isOffline ? null : ($isCritical ? -28.2 : -18.0 - (($i * 1.3) % 7.5));
            $tx = $isOffline ? null : 2.15;

            $onus[] = [
                'id' => "onu_hw_{$num}",
                'sn' => 'HWTC'.strtoupper(substr(md5("onu-sn-{$i}"), 0, 8)),
                'name' => "{$fn} {$ln}",
                'olt_id' => 'olt_kishuara_01',
                'board' => '0/1',
                'port' => (string) ((($i - 1) % 4) + 1),
                'onu_number' => $i,
                'zone' => $i <= 15 ? 'Jr. Bolognesi' : ($i <= 25 ? 'Av. Balta' : 'San José'),
                'odb' => 'NAP-'.str_pad((string) ((($i - 1) % 12) + 1), 2, '0', STR_PAD_LEFT),
                'status' => $status,
                'signal_rx' => $rx ? round($rx, 2) : null,
                'signal_tx' => $tx ? round($tx, 2) : null,
                'distance_m' => 450 + ($i * 65),
            ];
        }

        return $onus;
    }

    public function getOnuSignal(string $externalId): array
    {
        return [
            'external_id' => $externalId,
            'status' => 'Online',
            'signal_rx' => -19.45,
            'signal_tx' => 2.20,
            'temperature' => 43.0,
            'voltage' => 3.28,
            'bias_current' => 14.5,
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
