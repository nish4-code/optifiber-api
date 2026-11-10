<?php

namespace App\Services;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Models\NapBox;
use App\Models\NetworkAlert;
use App\Models\SmartOltOnu;

class NetworkAlertService
{
    public function evaluateNapCapacity(NapBox $napBox): ?NetworkAlert
    {
        $percentage = $napBox->occupancy_percentage;
        $activeAlert = NetworkAlert::where('alertable_type', NapBox::class)
            ->where('alertable_id', $napBox->id)
            ->where('type', AlertType::NapCapacity)
            ->whereNull('resolved_at')
            ->first();

        if ($percentage >= 85.0) {
            $severity = $percentage >= 87.0 ? AlertSeverity::Critical : AlertSeverity::Warning;
            $freePorts = $napBox->free_ports_count;
            $occupied = $napBox->occupied_ports_count;
            $total = $napBox->port_capacity;

            $message = "Capacidad al {$percentage}%. Queda sólo {$freePorts} puerto(s) libre(s) ({$occupied} de {$total} asignados).";

            if ($activeAlert) {
                $activeAlert->update([
                    'severity' => $severity,
                    'message' => $message,
                ]);

                return $activeAlert;
            }

            return NetworkAlert::create([
                'type' => AlertType::NapCapacity,
                'severity' => $severity,
                'title' => "{$napBox->code}: Capacidad al {$percentage}%",
                'message' => $message,
                'alertable_type' => NapBox::class,
                'alertable_id' => $napBox->id,
            ]);
        }

        if ($activeAlert) {
            $activeAlert->update(['resolved_at' => now()]);
        }

        return null;
    }

    public function evaluateOnuSignal(SmartOltOnu $onu): ?NetworkAlert
    {
        $activeAlert = NetworkAlert::where('alertable_type', SmartOltOnu::class)
            ->where('alertable_id', $onu->id)
            ->where('type', AlertType::OnuSignal)
            ->whereNull('resolved_at')
            ->first();

        $severity = null;
        $title = null;
        $message = null;

        if ($onu->status !== 'Online') {
            $severity = AlertSeverity::Critical;
            $title = "ONU {$onu->sn}: Estado {$onu->status}";
            $message = "La terminal óptica se encuentra en estado {$onu->status} en OLT {$onu->smartolt_olt_id} ({$onu->board}/{$onu->port}).";
        } elseif ($onu->signal_rx !== null && $onu->signal_rx <= -27.0) {
            $severity = AlertSeverity::Critical;
            $title = "ONU {$onu->sn}: Nivel óptico crítico ({$onu->signal_rx} dBm)";
            $message = "Potencia óptica recibida muy degradada ({$onu->signal_rx} dBm). Umbral crítico superado.";
        } elseif ($onu->signal_rx !== null && $onu->signal_rx <= -25.0) {
            $severity = AlertSeverity::Warning;
            $title = "ONU {$onu->sn}: Atenuación elevada ({$onu->signal_rx} dBm)";
            $message = "Potencia óptica recibida en límite de advertencia ({$onu->signal_rx} dBm).";
        }

        if ($severity !== null) {
            if ($activeAlert) {
                $activeAlert->update([
                    'severity' => $severity,
                    'title' => $title,
                    'message' => $message,
                ]);

                return $activeAlert;
            }

            return NetworkAlert::create([
                'type' => AlertType::OnuSignal,
                'severity' => $severity,
                'title' => $title,
                'message' => $message,
                'alertable_type' => SmartOltOnu::class,
                'alertable_id' => $onu->id,
            ]);
        }

        if ($activeAlert) {
            $activeAlert->update(['resolved_at' => now()]);
        }

        return null;
    }

    public function syncAllAlerts(): int
    {
        $count = 0;

        NapBox::with('ports')->chunk(100, function ($naps) use (&$count) {
            foreach ($naps as $nap) {
                if ($this->evaluateNapCapacity($nap)) {
                    $count++;
                }
            }
        });

        SmartOltOnu::chunk(100, function ($onus) use (&$count) {
            foreach ($onus as $onu) {
                if ($this->evaluateOnuSignal($onu)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
