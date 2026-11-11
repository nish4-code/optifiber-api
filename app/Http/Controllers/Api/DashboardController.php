<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClosureType;
use App\Enums\CustomerStatus;
use App\Enums\NetworkStatus;
use App\Enums\PortStatus;
use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\NapBox;
use App\Models\NapPort;
use App\Models\NetworkAlert;
use App\Models\SmartOltOlt;
use App\Models\SmartOltOnu;
use App\Models\SpliceClosure;
use App\Models\TrunkCable;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $napTotal = NapBox::count();
        $napActivas = NapBox::where('status', NetworkStatus::Active)->count();
        $napMant = NapBox::where('status', NetworkStatus::Maintenance)->count();
        $napInactivas = NapBox::where('status', NetworkStatus::Inactive)->count();

        $portsTotal = NapPort::count();
        $portsOccupied = NapPort::where('status', PortStatus::Occupied)->count();
        $napOccupancy = $portsTotal > 0 ? round(($portsOccupied / $portsTotal) * 100, 1) : 0;

        $zonesTotal = Zone::count();
        $trunksTotal = TrunkCable::count();
        $trunksKm = (float) TrunkCable::sum('length_km');

        $mufasTotal = SpliceClosure::count();
        $reservesTotal = SpliceClosure::where('type', ClosureType::Reserve)->count();
        $mufasActivas = SpliceClosure::where('status', NetworkStatus::Active)->count();
        $mufasMant = SpliceClosure::where('status', NetworkStatus::Maintenance)->count();
        $mufasInactivas = SpliceClosure::where('status', NetworkStatus::Inactive)->count();

        $customersTotal = Customer::count();
        $customersActivos = Customer::where('status', CustomerStatus::Active)->count();
        $customersSusp = Customer::where('status', CustomerStatus::Suspended)->count();
        $customersPend = Customer::where('status', CustomerStatus::Pending)->count();
        $customersNewThisMonth = Customer::where('installed_at', '>=', now()->startOfMonth())->count();

        $alertsActive = NetworkAlert::active()->count();

        $avgAttenuation = SmartOltOnu::whereNotNull('signal_rx')
            ->where('status', 'Online')
            ->avg('signal_rx');

        $olt = SmartOltOlt::first();
        $oltTraffic = $olt?->metrics['total_bandwidth_gbps'] ?? 9.84;

        $data = [
            'telemetry' => [
                'system_online' => true,
                'average_attenuation_dbm' => $avgAttenuation ? round($avgAttenuation, 1) : -19.4,
                'olt_bandwidth_gbps' => $oltTraffic,
            ],
            'kpis' => [
                'zones' => [
                    'total' => $zonesTotal,
                ],
                'trunks' => [
                    'total' => $trunksTotal,
                    'total_km' => round($trunksKm, 2),
                ],
                'reserves' => [
                    'total' => $reservesTotal,
                ],
                'naps' => [
                    'total' => $napTotal,
                    'occupancy_percentage' => $napOccupancy,
                    'active' => $napActivas,
                    'maintenance' => $napMant,
                    'inactive' => $napInactivas,
                ],
                'closures' => [
                    'total' => $mufasTotal,
                    'fusion_ok' => true,
                    'active' => $mufasActivas,
                    'maintenance' => $mufasMant,
                    'inactive' => $mufasInactivas,
                ],
                'customers' => [
                    'total' => $customersTotal,
                    'new_this_month' => $customersNewThisMonth,
                    'active' => $customersActivos,
                    'suspended' => $customersSusp,
                    'pending' => $customersPend,
                ],
                'network_traffic' => [
                    'peak_gbps' => 4.2,
                    'down_gbps' => 3.4,
                    'up_mbps' => 820,
                ],
                'active_alerts' => [
                    'count' => $alertsActive,
                    'attention_required' => $alertsActive > 0,
                ],
            ],
        ];

        return ApiResponseHelper::returnSuccess('Resumen de telemetría y KPIs obtenido.', $data)->send();
    }

    public function zonesCapacity(): JsonResponse
    {
        $zones = Zone::with(['napBoxes.ports'])->get()->map(function ($zone) {
            $totalPorts = 0;
            $occupiedPorts = 0;

            foreach ($zone->napBoxes as $nap) {
                $totalPorts += $nap->port_capacity;
                $occupiedPorts += $nap->ports->where('status', PortStatus::Occupied)->count();
            }

            $percentage = $totalPorts > 0 ? round(($occupiedPorts / $totalPorts) * 100, 1) : 0;

            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'total_ports' => $totalPorts,
                'occupied_ports' => $occupiedPorts,
                'free_ports' => $totalPorts - $occupiedPorts,
                'occupancy_percentage' => $percentage,
            ];
        });

        return ApiResponseHelper::returnSuccess('Capacidad por zonas obtenida.', ['zones' => $zones])->send();
    }

    public function latestCustomers(): JsonResponse
    {
        $customers = Customer::with(['port.napBox', 'zone'])
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'code' => $c->code,
                    'name' => $c->full_name,
                    'plan' => $c->plan,
                    'nap' => $c->port?->napBox?->code ?? 'Sin asignar',
                    'port' => $c->port ? "P{$c->port->port_number}" : null,
                    'status' => $c->status->value,
                    'installed_at' => $c->installed_at?->format('Y-m-d H:i'),
                ];
            });

        return ApiResponseHelper::returnSuccess('Últimos abonados obtenidos.', ['customers' => $customers])->send();
    }
}
