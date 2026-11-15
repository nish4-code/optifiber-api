<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SmartOltEvent;
use App\Models\SmartOltOdb;
use App\Models\SmartOltOlt;
use App\Models\SmartOltOnu;
use App\Models\SmartOltSpeedProfile;
use App\Models\SmartOltZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartOltSeeder extends Seeder
{
    public function run(): void
    {
        $oltsData = [
            [
                'external_id' => '1',
                'name' => 'OLT-Chiclayo-Norte',
                'olt_hardware_version' => 'Huawei-MA5800-X7',
                'ip' => '192.168.10.2',
                'telnet_port' => 2323,
                'snmp_port' => 2161,
                'status' => 'online',
                'latitude' => -6.76200,
                'longitude' => -79.83500,
                'metrics' => [
                    'cpu_usage' => 16,
                    'memory_usage' => 32,
                    'temperature' => 40.2,
                    'pon_ports_total' => 16,
                    'pon_ports_active' => 14,
                ],
            ],
            [
                'external_id' => '2',
                'name' => 'OLT-Chiclayo-Sur',
                'olt_hardware_version' => 'Huawei-MA5680T',
                'ip' => '192.168.10.3',
                'telnet_port' => 2323,
                'snmp_port' => 2161,
                'status' => 'online',
                'latitude' => -6.76200,
                'longitude' => -79.83500,
                'metrics' => [
                    'cpu_usage' => 22,
                    'memory_usage' => 45,
                    'temperature' => 42.0,
                    'pon_ports_total' => 16,
                    'pon_ports_active' => 12,
                ],
            ],
            [
                'external_id' => '3',
                'name' => 'OLT-Lambayeque-Centro',
                'olt_hardware_version' => 'ZTE-C320',
                'ip' => '192.168.20.2',
                'telnet_port' => 2323,
                'snmp_port' => 2161,
                'status' => 'online',
                'latitude' => -6.76200,
                'longitude' => -79.83500,
                'metrics' => [
                    'cpu_usage' => 11,
                    'memory_usage' => 24,
                    'temperature' => 38.1,
                    'pon_ports_total' => 8,
                    'pon_ports_active' => 6,
                ],
            ],
            [
                'external_id' => '4',
                'name' => 'OLT-Pimentel-Balneario',
                'olt_hardware_version' => 'ZTE-C300',
                'ip' => '192.168.30.2',
                'telnet_port' => 2323,
                'snmp_port' => 2161,
                'status' => 'online',
                'latitude' => -6.76200,
                'longitude' => -79.83500,
                'metrics' => [
                    'cpu_usage' => 8,
                    'memory_usage' => 19,
                    'temperature' => 36.5,
                    'pon_ports_total' => 8,
                    'pon_ports_active' => 5,
                ],
            ],
        ];

        $olts = [];
        foreach ($oltsData as $olt) {
            $olts[$olt['external_id']] = SmartOltOlt::updateOrCreate(
                ['external_id' => $olt['external_id']],
                array_merge($olt, ['last_synced_at' => now()])
            );
        }

        $zonesData = [
            ['external_id' => '1', 'name' => 'Centro Historico', 'description' => 'Zona urbana comercial y centro'],
            ['external_id' => '2', 'name' => 'Santa Victoria', 'description' => 'Zona residencial alta densidad'],
            ['external_id' => '3', 'name' => 'Pimentel Balneario', 'description' => 'Zona turistica costera'],
            ['external_id' => '4', 'name' => 'La Victoria', 'description' => 'Zona sur de Chiclayo'],
            ['external_id' => '5', 'name' => 'Jose Leonardo Ortiz', 'description' => 'Zona comercial e industrial norte'],
            ['external_id' => '6', 'name' => 'Las Musas', 'description' => 'Sector residencial central'],
            ['external_id' => '7', 'name' => 'Campodonico', 'description' => 'Zona este urbana'],
            ['external_id' => '8', 'name' => 'San Jose', 'description' => 'Sector costero'],
            ['external_id' => '9', 'name' => 'Reque', 'description' => 'Conexion troncal sur'],
            ['external_id' => '10', 'name' => 'Monsefu', 'description' => 'Circuito tradicional sur-oeste'],
        ];

        $zones = [];
        foreach ($zonesData as $zd) {
            $zones[$zd['external_id']] = SmartOltZone::updateOrCreate(
                ['external_id' => $zd['external_id']],
                $zd
            );
        }

        $profilesData = [
            ['external_id' => '1', 'name' => 'Plan Hogar 50M', 'download_speed' => '51200', 'upload_speed' => '25600'],
            ['external_id' => '2', 'name' => 'Plan Duo 100M', 'download_speed' => '102400', 'upload_speed' => '51200'],
            ['external_id' => '3', 'name' => 'Plan Gamer 200M', 'download_speed' => '204800', 'upload_speed' => '102400'],
            ['external_id' => '4', 'name' => 'Plan Ultra 300M', 'download_speed' => '307200', 'upload_speed' => '153600'],
            ['external_id' => '5', 'name' => 'Plan Corporativo 500M', 'download_speed' => '512000', 'upload_speed' => '512000'],
            ['external_id' => '6', 'name' => 'Plan Dedicado 1Gbps', 'download_speed' => '1024000', 'upload_speed' => '1024000'],
        ];

        foreach ($profilesData as $pd) {
            SmartOltSpeedProfile::updateOrCreate(
                ['external_id' => $pd['external_id']],
                $pd
            );
        }

        $odbs = [];
        for ($i = 1; $i <= 64; $i++) {
            $num = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $zoneId = (string) ((($i - 1) % 10) + 1);
            $oltId = (string) ((($i - 1) % 4) + 1);
            $boardNum = (int) ((($i - 1) / 16) % 2);
            $portNum = (int) ((($i - 1) % 16) + 1);

            $odbs[$i] = SmartOltOdb::updateOrCreate(
                ['external_id' => "odb_{$num}"],
                [
                    'name' => "NAP-{$num}",
                    'zone_id' => $zones[$zoneId]->id,
                    'smartolt_olt_id' => $olts[$oltId]->id,
                    'pon_port' => "0/{$boardNum}/{$portNum}",
                    'total_ports' => 16,
                    'used_ports' => rand(6, 15),
                    'latitude' => round(-6.75200 - fmod($i * 0.0018, 0.075), 6),
                    'longitude' => round(-79.82500 - fmod($i * 0.0022, 0.090), 6),
                ]
            );
        }

        $firstNames = [
            'Juan', 'Carlos', 'Maria', 'Rosa', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Lucia', 'Jorge',
            'Sonia', 'Miguel', 'Elena', 'Victor', 'Patricia', 'Raul', 'Gloria', 'Cesar', 'Martha',
            'Diego', 'Silvia', 'Manuel', 'Adriana', 'Fernando', 'Teresa', 'Hector', 'Rocio', 'Oscar',
            'Vanessa', 'Alberto', 'Milagros', 'Javier', 'Fiorella', 'Guillermo', 'Karina', 'Christian'
        ];

        $lastNames = [
            'Perez', 'Torres', 'Garcia', 'Flores', 'Mendoza', 'Quispe', 'Fernandez', 'Chavez',
            'Ramirez', 'Diaz', 'Morales', 'Herrera', 'Paredes', 'Castro', 'Vega', 'Rios', 'Guzman',
            'Salazar', 'Campos', 'Navarro', 'Cordova', 'Delgado', 'Vargas', 'Reyes', 'Castillo'
        ];

        $hardwareCatalog = [
            ['vendor' => 'Huawei', 'prefix' => 'HWTC', 'type_id' => '308', 'type_name' => 'HG8546M'],
            ['vendor' => 'Huawei', 'prefix' => 'HWTC', 'type_id' => '412', 'type_name' => 'EG8145V5'],
            ['vendor' => 'Huawei', 'prefix' => 'HWTC', 'type_id' => '205', 'type_name' => 'HG8245H'],
            ['vendor' => 'ZTE', 'prefix' => 'ZTEG', 'type_id' => '75', 'type_name' => 'F660V6.0'],
            ['vendor' => 'ZTE', 'prefix' => 'ZTEG', 'type_id' => '118', 'type_name' => 'F670L'],
            ['vendor' => 'Fiberhome', 'prefix' => 'FHTT', 'type_id' => '92', 'type_name' => 'AN5506-04-B'],
            ['vendor' => 'VSOL', 'prefix' => 'VSOL', 'type_id' => '64', 'type_name' => 'V2804'],
        ];

        $streets = [
            'Av. Balta', 'Av. Salaverry', 'Calle Los Cedros', 'Av. Bolognesi', 'Calle San Jose',
            'Av. Saenz Pena', 'Calle Manuel Maria Izaga', 'Av. Jose Leonardo Ortiz', 'Calle Tacna',
            'Av. Mariscal Nieto', 'Av. Chinchaysuyo', 'Calle Grau', 'Av. Sesquicentenario'
        ];

        $onusBatch = [];
        $now = now()->toIso8601String();

        for ($i = 1; $i <= 1000; $i++) {
            $num = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $fn = $firstNames[($i * 7) % count($firstNames)];
            $ln = $lastNames[($i * 13) % count($lastNames)];
            $street = $streets[($i * 5) % count($streets)];
            $streetNumber = (($i * 17) % 850) + 100;

            $assignedOdb = $odbs[((($i - 1) % 64) + 1)];
            $assignedOltId = $assignedOdb->smartolt_olt_id;
            $assignedZoneId = $assignedOdb->zone_id;

            $hw = $hardwareCatalog[($i * 3) % count($hardwareCatalog)];
            $snSuffix = strtoupper(substr(md5("sn-generator-seed-{$i}"), 0, 8));
            $sn = "{$hw['prefix']}{$snSuffix}";

            $isLos = ($i % 25 === 0);
            $isCritical = ($i % 17 === 0 && ! $isLos);
            $isPowerFail = ($i % 37 === 0 && ! $isLos && ! $isCritical);
            $isDisabled = ($i % 53 === 0 && ! $isLos && ! $isCritical && ! $isPowerFail);

            if ($isLos) {
                $status = 'LOS';
                $adminStatus = 'Enabled';
                $rx = null;
                $tx = null;
                $signalValue = 'LOS';
                $sig1310 = 'N/A';
                $sig1490 = 'N/A';
            } elseif ($isPowerFail) {
                $status = 'PowerFail';
                $adminStatus = 'Enabled';
                $rx = null;
                $tx = null;
                $signalValue = 'Power off';
                $sig1310 = 'N/A';
                $sig1490 = 'N/A';
            } elseif ($isDisabled) {
                $status = 'Disabled';
                $adminStatus = 'Disabled';
                $rx = -21.40;
                $tx = 2.10;
                $signalValue = '-21.40 dBm / 2.10 dBm';
                $sig1310 = '2.10 dBm';
                $sig1490 = '-21.40 dBm';
            } elseif ($isCritical) {
                $status = 'Online';
                $adminStatus = 'Enabled';
                $rx = round(-27.50 - fmod($i * 0.25, 4.3), 2);
                $tx = 2.45;
                $signalValue = "{$rx} dBm / {$tx} dBm";
                $sig1310 = "{$tx} dBm";
                $sig1490 = "{$rx} dBm";
            } else {
                $status = 'Online';
                $adminStatus = 'Enabled';
                $rx = round(-16.20 - fmod($i * 0.31, 6.8), 2);
                $tx = 2.20;
                $signalValue = "{$rx} dBm / {$tx} dBm";
                $sig1310 = "{$tx} dBm";
                $sig1490 = "{$rx} dBm";
            }

            $board = '0/' . ((int) (($i - 1) / 500) % 2);
            $port = (string) (((int) (($i - 1) / 32) % 16) + 1);
            $onuNumber = ((($i - 1) % 32) + 1);

            $lat = round(-6.75000 - fmod($i * 0.00035, 0.075), 6);
            $lng = round(-79.82000 - fmod($i * 0.00045, 0.090), 6);

            $onusBatch[] = [
                'id' => (string) Str::uuid(),
                'unique_external_id' => "onu_{$hw['prefix']}_{$num}",
                'sn' => $sn,
                'name' => "{$fn} {$ln} - Clie {$num}",
                'smartolt_olt_id' => $assignedOltId,
                'zone_id' => $assignedZoneId,
                'odb_id' => $assignedOdb->id,
                'pon_type' => 'gpon',
                'board' => $board,
                'port' => $port,
                'onu_number' => $onuNumber,
                'onu_type_id' => $hw['type_id'],
                'onu_type_name' => $hw['type_name'],
                'status' => $status,
                'administrative_status' => $adminStatus,
                'catv_status' => null,
                'signal_value' => $signalValue,
                'signal_1310' => $sig1310,
                'signal_1490' => $sig1490,
                'signal_rx' => $rx,
                'signal_tx' => $tx,
                'distance_m' => 180 + (($i * 23) % 4500),
                'mode' => 'Routing',
                'vlan' => 100 + (($i % 6) * 20),
                'address' => "{$street} {$streetNumber}, Chiclayo",
                'latitude' => $lat,
                'longitude' => $lng,
                'extra_data' => json_encode(['batch' => 'dev_seed_1000', 'index' => $i]),
                'last_synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('smartolt_onus')->truncate();

        foreach (array_chunk($onusBatch, 250) as $chunk) {
            DB::table('smartolt_onus')->insert($chunk);
        }

        SmartOltEvent::create([
            'event_type' => 'pon.outage_started',
            'onu_sn' => null,
            'olt_id' => '1',
            'severity' => 'critical',
            'payload' => [
                'board' => '0/0',
                'port' => '4',
                'affected_onus_count' => 32,
                'fiber_cut_detected' => true,
            ],
            'occurred_at' => now()->subMinutes(45),
        ]);

        SmartOltEvent::create([
            'event_type' => 'onu.status_changed',
            'onu_sn' => $onusBatch[24]['sn'],
            'olt_id' => '1',
            'severity' => 'warning',
            'payload' => [
                'previous_status' => 'Online',
                'current_status' => 'LOS',
                'attenuation_rx' => -32.4,
            ],
            'occurred_at' => now()->subMinutes(12),
        ]);
    }
}
