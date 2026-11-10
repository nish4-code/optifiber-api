<?php

namespace App\Services;

use App\Enums\PortStatus;
use App\Enums\StrandStatus;
use App\Models\FiberBuffer;
use App\Models\FiberColor;
use App\Models\FiberStrand;
use App\Models\NapBox;
use App\Models\NapPort;
use App\Models\TrunkCable;
use Illuminate\Support\Facades\DB;

class FiberTopologyService
{
    public function generateBuffersAndStrands(TrunkCable $cable): void
    {
        DB::transaction(function () use ($cable) {
            $colors = FiberColor::orderBy('position')->get()->keyBy('position');

            for ($b = 1; $b <= $cable->buffer_count; $b++) {
                $bufferColor = $colors->get((($b - 1) % 12) + 1);

                $buffer = FiberBuffer::updateOrCreate(
                    [
                        'trunk_cable_id' => $cable->id,
                        'buffer_number' => $b,
                    ],
                    [
                        'fiber_color_id' => $bufferColor->id,
                    ]
                );

                for ($s = 1; $s <= $cable->strands_per_buffer; $s++) {
                    $globalNumber = $this->calculateGlobalStrandNumber($b, $s);
                    $strandColor = $colors->get((($s - 1) % 12) + 1);

                    FiberStrand::updateOrCreate(
                        [
                            'trunk_cable_id' => $cable->id,
                            'global_number' => $globalNumber,
                        ],
                        [
                            'fiber_buffer_id' => $buffer->id,
                            'strand_number' => $s,
                            'fiber_color_id' => $strandColor->id,
                            'status' => StrandStatus::Free,
                        ]
                    );
                }
            }
        });
    }

    public function generateNapPorts(NapBox $napBox): void
    {
        DB::transaction(function () use ($napBox) {
            for ($p = 1; $p <= $napBox->port_capacity; $p++) {
                NapPort::firstOrCreate(
                    [
                        'nap_box_id' => $napBox->id,
                        'port_number' => $p,
                    ],
                    [
                        'status' => PortStatus::Free,
                    ]
                );
            }
        });
    }

    public function calculateGlobalStrandNumber(int $bufferNumber, int $strandNumber): int
    {
        return (($bufferNumber - 1) * 12) + $strandNumber;
    }

    public function traceOpticalPath(FiberStrand $strand): array
    {
        $strand->load(['buffer.color', 'color', 'trunkCable.zone', 'splicesAsIn.closure', 'splicesAsIn.napBox.ports.customer']);

        $hops = [];
        $hops[] = [
            'type' => 'cable',
            'cable_id' => $strand->trunkCable->id,
            'cable_code' => $strand->trunkCable->code,
            'cable_name' => $strand->trunkCable->name,
            'buffer_number' => $strand->buffer->buffer_number,
            'buffer_color' => $strand->buffer->color->name,
            'strand_number' => $strand->strand_number,
            'global_number' => $strand->global_number,
            'strand_color' => $strand->color->name,
            'status' => $strand->status->value,
        ];

        foreach ($strand->splicesAsIn as $splice) {
            $hops[] = [
                'type' => 'splice',
                'closure_code' => $splice->closure?->code,
                'closure_name' => $splice->closure?->name,
                'tray_number' => $splice->tray_number,
                'loss_db' => $splice->loss_db,
            ];

            if ($splice->napBox) {
                $hops[] = [
                    'type' => 'nap',
                    'nap_code' => $splice->napBox->code,
                    'nap_name' => $splice->napBox->name,
                    'splitter_ratio' => $splice->napBox->splitter_ratio->value,
                    'occupancy' => "{$splice->napBox->occupied_ports_count}/{$splice->napBox->port_capacity}",
                ];
            }
        }

        return [
            'strand_id' => $strand->id,
            'global_number' => $strand->global_number,
            'path' => $hops,
        ];
    }
}
