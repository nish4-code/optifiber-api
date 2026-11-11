<?php

namespace Database\Seeders;

use App\Enums\ClosureType;
use App\Enums\CustomerStatus;
use App\Enums\MountType;
use App\Enums\NetworkStatus;
use App\Enums\PortStatus;
use App\Enums\SplitterRatio;
use App\Enums\StrandStatus;
use App\Models\Customer;
use App\Models\CustomerEvent;
use App\Models\FiberSplice;
use App\Models\FiberStrand;
use App\Models\NapBox;
use App\Models\NapPort;
use App\Models\SpliceClosure;
use App\Models\TrunkCable;
use App\Models\Zone;
use App\Services\FiberTopologyService;
use App\Services\NetworkAlertService;
use Illuminate\Database\Seeder;

class DemoNetworkSeeder extends Seeder
{
    public function run(FiberTopologyService $topologyService, NetworkAlertService $alertService): void
    {
        $zoneKish = Zone::where('code', 'ZON-KISH')->first();
        $zoneBolog = Zone::where('code', 'ZON-BOLOG')->first();
        $zoneBalta = Zone::where('code', 'ZON-BALTA')->first();

        // 1. Troncales Principales
        $trunk1 = TrunkCable::updateOrCreate(
            ['code' => 'TRK-45'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Línea 45',
                'fiber_count' => 144,
                'buffer_count' => 12,
                'strands_per_buffer' => 12,
                'path' => [
                    [-6.7710, -79.8400],
                    [-6.7725, -79.8415],
                    [-6.7740, -79.8430],
                ],
                'length_km' => 1.240,
                'map_color' => '#3B82F6',
                'status' => NetworkStatus::Active,
                'is_visible' => true,
            ]
        );
        $topologyService->generateBuffersAndStrands($trunk1);

        $trunk2 = TrunkCable::updateOrCreate(
            ['code' => 'TRK-46'],
            [
                'zone_id' => $zoneBalta?->id,
                'name' => 'Línea 46',
                'fiber_count' => 48,
                'buffer_count' => 4,
                'strands_per_buffer' => 12,
                'path' => [
                    [-6.7740, -79.8430],
                    [-6.7755, -79.8450],
                    [-6.7770, -79.8465],
                ],
                'length_km' => 0.980,
                'map_color' => '#10B981',
                'status' => NetworkStatus::Active,
                'is_visible' => true,
            ]
        );
        $topologyService->generateBuffersAndStrands($trunk2);

        $trunk3 = TrunkCable::updateOrCreate(
            ['code' => 'TRK-47'],
            [
                'zone_id' => $zoneKish?->id,
                'name' => 'Línea 47',
                'fiber_count' => 144,
                'buffer_count' => 12,
                'strands_per_buffer' => 12,
                'path' => [
                    [-13.6420, -73.0850],
                    [-13.6441, -73.0871],
                    [-13.6460, -73.0890],
                ],
                'length_km' => 1.100,
                'map_color' => '#F59E0B',
                'status' => NetworkStatus::Active,
                'is_visible' => true,
            ]
        );
        $topologyService->generateBuffersAndStrands($trunk3);

        // 2. Mufas y Mangas de Empalme
        $mufa1 = SpliceClosure::updateOrCreate(
            ['code' => 'MUFA-01'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Mufa Troncal Central 01',
                'type' => ClosureType::Splice,
                'mount_type' => MountType::Pole,
                'fiber_capacity' => 144,
                'latitude' => -6.7725000,
                'longitude' => -79.8415000,
                'reserve_meters' => 15.0,
                'status' => NetworkStatus::Active,
            ]
        );

        $mufa2 = SpliceClosure::updateOrCreate(
            ['code' => 'MUFA-02'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Mufa Distribución Bolognesi',
                'type' => ClosureType::Distribution,
                'mount_type' => MountType::Chamber,
                'fiber_capacity' => 48,
                'latitude' => -6.7735000,
                'longitude' => -79.8425000,
                'reserve_meters' => 10.0,
                'status' => NetworkStatus::Active,
            ]
        );

        $mufa8 = SpliceClosure::updateOrCreate(
            ['code' => 'MUF-08'],
            [
                'zone_id' => $zoneBalta?->id,
                'name' => 'Mufa Balta Sur',
                'type' => ClosureType::Splice,
                'mount_type' => MountType::Pole,
                'fiber_capacity' => 144,
                'latitude' => -6.7760000,
                'longitude' => -79.8440000,
                'reserve_meters' => 20.0,
                'status' => NetworkStatus::Maintenance,
            ]
        );

        $mufa3 = SpliceClosure::updateOrCreate(
            ['code' => 'MUFA-03'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Mufa Troncal San José 96',
                'type' => ClosureType::Splice,
                'mount_type' => MountType::Pole,
                'fiber_capacity' => 96,
                'latitude' => -6.7720000,
                'longitude' => -79.8410000,
                'reserve_meters' => 15.0,
                'status' => NetworkStatus::Active,
            ]
        );

        $mufa4 = SpliceClosure::updateOrCreate(
            ['code' => 'MUFA-04'],
            [
                'zone_id' => $zoneKish?->id,
                'name' => 'Mufa Matriz Central Kishuara',
                'type' => ClosureType::Splice,
                'mount_type' => MountType::Chamber,
                'fiber_capacity' => 144,
                'latitude' => -13.6540000,
                'longitude' => -73.3440000,
                'reserve_meters' => 25.0,
                'status' => NetworkStatus::Active,
            ]
        );

        // 3. Cajas NAP
        $feederStrand1 = FiberStrand::where('trunk_cable_id', $trunk1->id)->where('global_number', 1)->first();
        $feederStrand2 = FiberStrand::where('trunk_cable_id', $trunk1->id)->where('global_number', 2)->first();

        if ($feederStrand1) {
            $feederStrand1->update(['status' => StrandStatus::Occupied]);
        }
        if ($feederStrand2) {
            $feederStrand2->update(['status' => StrandStatus::Occupied]);
        }

        $nap4 = NapBox::updateOrCreate(
            ['code' => 'NAP-04'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'NAP 1 Ruta 4',
                'splitter_ratio' => SplitterRatio::Ratio1x8,
                'port_capacity' => 8,
                'address' => 'Jr. Bolognesi 458, Chiclayo',
                'latitude' => -6.7730000,
                'longitude' => -79.8420000,
                'feeder_strand_id' => $feederStrand1?->id,
                'map_color' => '#EF4444',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Alert,
                'notes' => 'Capacidad alta (7 de 8 asignados)',
            ]
        );
        $topologyService->generateNapPorts($nap4);

        $nap5 = NapBox::updateOrCreate(
            ['code' => 'NAP-05'],
            [
                'zone_id' => $zoneBalta?->id,
                'name' => 'NAP 1 Ruta 5',
                'splitter_ratio' => SplitterRatio::Ratio1x8,
                'port_capacity' => 8,
                'address' => 'Av. Balta 1020, Chiclayo',
                'latitude' => -6.7750000,
                'longitude' => -79.8435000,
                'feeder_strand_id' => $feederStrand2?->id,
                'map_color' => '#10B981',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Active,
                'notes' => 'Splitter primario operativo',
            ]
        );
        $topologyService->generateNapPorts($nap5);

        // NAPs adicionales para catálogo completo
        $feederStrand3 = FiberStrand::where('trunk_cable_id', $trunk1->id)->where('global_number', 3)->first();
        $feederStrand4 = FiberStrand::where('trunk_cable_id', $trunk1->id)->where('global_number', 4)->first();
        $feederStrand5 = FiberStrand::where('trunk_cable_id', $trunk2->id)->where('global_number', 1)->first();

        $nap1 = NapBox::updateOrCreate(
            ['code' => 'NAP-01'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Bolognesi esq. San José',
                'splitter_ratio' => SplitterRatio::Ratio1x8,
                'port_capacity' => 8,
                'address' => 'Jr. Bolognesi 345, Chiclayo',
                'latitude' => -6.7715000,
                'longitude' => -79.8405000,
                'feeder_strand_id' => $feederStrand3?->id,
                'map_color' => '#10B981',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Active,
                'notes' => 'Caja terminal principal San José',
            ]
        );
        $topologyService->generateNapPorts($nap1);

        $nap2 = NapBox::updateOrCreate(
            ['code' => 'NAP-02'],
            [
                'zone_id' => $zoneBolog?->id,
                'name' => 'Bolognesi / Av. Saenz Peña',
                'splitter_ratio' => SplitterRatio::Ratio1x16,
                'port_capacity' => 16,
                'address' => 'Jr. Bolognesi 580, Chiclayo',
                'latitude' => -6.7738000,
                'longitude' => -79.8428000,
                'feeder_strand_id' => $feederStrand4?->id,
                'map_color' => '#10B981',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Active,
                'notes' => 'Splitter 1x16 de alta densidad',
            ]
        );
        $topologyService->generateNapPorts($nap2);

        $nap3 = NapBox::updateOrCreate(
            ['code' => 'NAP-03'],
            [
                'zone_id' => $zoneBalta?->id,
                'name' => 'Av. Balta Frente CC Boulevard',
                'splitter_ratio' => SplitterRatio::Ratio1x8,
                'port_capacity' => 8,
                'address' => 'Av. Balta 720, Chiclayo',
                'latitude' => -6.7765000,
                'longitude' => -79.8445000,
                'feeder_strand_id' => $feederStrand5?->id,
                'map_color' => '#EF4444',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Alert,
                'notes' => 'Caja saturada al 100% de puertos',
            ]
        );
        $topologyService->generateNapPorts($nap3);

        $nap6 = NapBox::updateOrCreate(
            ['code' => 'NAP-06'],
            [
                'zone_id' => $zoneKish?->id,
                'name' => 'Plaza Principal Kishuara',
                'splitter_ratio' => SplitterRatio::Ratio1x8,
                'port_capacity' => 8,
                'address' => 'Plaza de Armas S/N, Kishuara, Apurímac',
                'latitude' => -13.6550000,
                'longitude' => -73.3450000,
                'map_color' => '#3B82F6',
                'map_icon' => 'nap',
                'status' => NetworkStatus::Maintenance,
                'notes' => 'Mantenimiento preventivo programado',
            ]
        );
        $topologyService->generateNapPorts($nap6);

        // 4. Fusiones de Hilos (Matriz)
        if ($feederStrand1) {
            FiberSplice::updateOrCreate(
                ['splice_closure_id' => $mufa1->id, 'strand_in_id' => $feederStrand1->id],
                [
                    'tray_number' => 1,
                    'nap_box_id' => $nap4->id,
                    'loss_db' => 0.04,
                    'status' => NetworkStatus::Active,
                ]
            );
        }

        if ($feederStrand2) {
            FiberSplice::updateOrCreate(
                ['splice_closure_id' => $mufa2->id, 'strand_in_id' => $feederStrand2->id],
                [
                    'tray_number' => 1,
                    'nap_box_id' => $nap5->id,
                    'loss_db' => 0.03,
                    'status' => NetworkStatus::Active,
                ]
            );
        }

        // 5. Clientes de Prueba (conectados a puertos NAP y SmartOLT)
        $nap4Ports = NapPort::where('nap_box_id', $nap4->id)->orderBy('port_number')->get();
        $nap5Ports = NapPort::where('nap_box_id', $nap5->id)->orderBy('port_number')->get();
        $nap1Ports = NapPort::where('nap_box_id', $nap1->id)->orderBy('port_number')->get();
        $nap2Ports = NapPort::where('nap_box_id', $nap2->id)->orderBy('port_number')->get();
        $nap3Ports = NapPort::where('nap_box_id', $nap3->id)->orderBy('port_number')->get();

        $demoCustomers = [
            ['code' => 'C-001', 'name' => 'Juan Pérez García', 'doc' => '45892314', 'phone' => '987 654 321', 'plan' => '100 Mbps', 'port' => $nap4Ports->get(0), 'onu_id' => 'onu_hw_001', 'status' => CustomerStatus::Active],
            ['code' => 'C-002', 'name' => 'María García Quispe', 'doc' => '70124589', 'phone' => '912 345 678', 'plan' => '50 Mbps', 'port' => $nap4Ports->get(1), 'onu_id' => 'onu_hw_002', 'status' => CustomerStatus::Active],
            ['code' => 'C-003', 'name' => 'Carlos López Mendoza', 'doc' => '10235478', 'phone' => '998 776 022', 'plan' => '100 Mbps', 'port' => $nap4Ports->get(2), 'onu_id' => 'onu_hw_003', 'status' => CustomerStatus::Active],
            ['code' => 'C-004', 'name' => 'Ana Torres Ramírez', 'doc' => '44879512', 'phone' => '901 223 445', 'plan' => '50 Mbps', 'port' => $nap4Ports->get(3), 'onu_id' => 'onu_hw_004', 'status' => CustomerStatus::Active],
            ['code' => 'C-005', 'name' => 'Jorge Vargas Llacta', 'doc' => '41258963', 'phone' => '944 556 778', 'plan' => '200 Mbps', 'port' => $nap4Ports->get(4), 'onu_id' => 'onu_hw_005', 'status' => CustomerStatus::Active],
            ['code' => 'C-006', 'name' => 'Rosa Flores León', 'doc' => '72365412', 'phone' => '955 667 889', 'plan' => '100 Mbps', 'port' => $nap4Ports->get(5), 'onu_id' => 'onu_hw_006', 'status' => CustomerStatus::Active],
            ['code' => 'C-007', 'name' => 'Luis Mendoza Castro', 'doc' => '09852147', 'phone' => '966 778 990', 'plan' => '50 Mbps', 'port' => $nap4Ports->get(6), 'onu_id' => 'onu_hw_007', 'status' => CustomerStatus::Active], // 7mo puerto ocupado en NAP-04 (87.5% - Alerta)
            ['code' => 'C-008', 'name' => 'Carmen Quispe Solano', 'doc' => '47852369', 'phone' => '977 889 001', 'plan' => '100 Mbps', 'port' => $nap5Ports->get(0), 'onu_id' => 'onu_hw_008', 'status' => CustomerStatus::Active],
            ['code' => 'C-009', 'name' => 'Pedro Solano Ramos', 'doc' => '48963254', 'phone' => '988 990 112', 'plan' => '200 Mbps', 'port' => $nap5Ports->get(1), 'onu_id' => 'onu_hw_009', 'status' => CustomerStatus::Active],
            ['code' => 'C-010', 'name' => 'Lucía Ramos Vega', 'doc' => '71452369', 'phone' => '999 001 223', 'plan' => '50 Mbps', 'port' => $nap5Ports->get(2), 'onu_id' => 'onu_hw_010', 'status' => CustomerStatus::Active],
            ['code' => 'C-011', 'name' => 'Ana Belén Silva', 'doc' => '73215498', 'phone' => '944 112 334', 'plan' => '100 Mbps', 'port' => $nap1Ports->get(0), 'onu_id' => null, 'status' => CustomerStatus::Pending],
            ['code' => 'C-012', 'name' => 'Roberto Chang Díaz', 'doc' => '08745123', 'phone' => '955 223 445', 'plan' => '200 Mbps', 'port' => $nap2Ports->get(0), 'onu_id' => null, 'status' => CustomerStatus::Suspended],
            ['code' => 'C-013', 'name' => 'Diana Morales Cueva', 'doc' => '46852179', 'phone' => '966 334 556', 'plan' => '50 Mbps', 'port' => $nap2Ports->get(1), 'onu_id' => null, 'status' => CustomerStatus::Active],
            ['code' => 'C-014', 'name' => 'Javier Espinoza Paz', 'doc' => '70145896', 'phone' => '977 445 667', 'plan' => '100 Mbps', 'port' => $nap3Ports->get(0), 'onu_id' => null, 'status' => CustomerStatus::Active],
        ];

        foreach ($demoCustomers as $c) {
            $port = $c['port'];
            if ($port) {
                $port->update(['status' => PortStatus::Occupied]);
            }

            $customer = Customer::updateOrCreate(
                ['code' => $c['code']],
                [
                    'zone_id' => $zoneBolog?->id,
                    'full_name' => $c['name'],
                    'document' => $c['doc'],
                    'phone' => $c['phone'],
                    'address' => 'Jr. Bolognesi, Chiclayo',
                    'latitude' => -6.7725 + (rand(-50, 50) / 10000),
                    'longitude' => -79.8415 + (rand(-50, 50) / 10000),
                    'plan' => $c['plan'],
                    'nap_port_id' => $port?->id,
                    'smartolt_onu_external_id' => $c['onu_id'],
                    'status' => $c['status'] ?? CustomerStatus::Active,
                    'installed_at' => now()->subDays(rand(5, 60)),
                ]
            );

            CustomerEvent::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'type' => 'instalacion',
                ],
                [
                    'description' => "Instalación completada y activada en puerto {$port?->port_number}.",
                    'payload' => ['plan' => $c['plan'], 'onu_id' => $c['onu_id']],
                    'occurred_at' => $customer->installed_at ?? now(),
                ]
            );
        }

        // Evaluar alertas en NAPs
        $alertService->evaluateNapCapacity($nap1);
        $alertService->evaluateNapCapacity($nap2);
        $alertService->evaluateNapCapacity($nap3);
        $alertService->evaluateNapCapacity($nap4);
        $alertService->evaluateNapCapacity($nap5);
        $alertService->evaluateNapCapacity($nap6);
    }
}
