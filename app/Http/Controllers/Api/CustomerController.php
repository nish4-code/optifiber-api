<?php

namespace App\Http\Controllers\Api;

use App\Enums\CustomerStatus;
use App\Enums\PortStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerEvent;
use App\Models\NapPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['zone:id,name,code', 'port.napBox:id,code,name', 'onu'])->orderBy('code');

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->input('zone_id'));
        }

        if ($request->filled('nap_box_id')) {
            $napBoxId = $request->input('nap_box_id');
            $query->whereHas('port', function ($q) use ($napBoxId) {
                $q->where('nap_box_id', $napBoxId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                    ->orWhere('full_name', 'ilike', "%{$search}%")
                    ->orWhere('document', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->through(function ($c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'full_name' => $c->full_name,
                'document' => $c->document,
                'phone' => $c->phone,
                'address' => $c->address,
                'plan' => $c->plan,
                'zone' => $c->zone,
                'nap' => $c->port?->napBox ? [
                    'id' => $c->port->napBox->id,
                    'code' => $c->port->napBox->code,
                    'name' => $c->port->napBox->name,
                    'port_number' => $c->port->port_number,
                ] : null,
                'smartolt' => $c->onu ? [
                    'sn' => $c->onu->sn,
                    'status' => $c->onu->status,
                    'signal_rx' => $c->onu->signal_rx,
                ] : null,
                'status' => $c->status->value,
                'installed_at' => $c->installed_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'nullable|exists:zones,id',
            'code' => 'required|string|max:50|unique:customers,code',
            'full_name' => 'required|string|max:150',
            'document' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'plan' => 'nullable|string|max:50',
            'nap_port_id' => 'nullable|exists:nap_ports,id|unique:customers,nap_port_id',
            'smartolt_onu_external_id' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::enum(CustomerStatus::class)],
        ]);

        $customer = Customer::create([
            ...$validated,
            'plan' => $validated['plan'] ?? '100 Mbps',
            'status' => $validated['status'] ?? CustomerStatus::Active,
            'installed_at' => now(),
        ]);

        if (! empty($validated['nap_port_id'])) {
            NapPort::where('id', $validated['nap_port_id'])->update(['status' => PortStatus::Occupied]);
        }

        CustomerEvent::create([
            'customer_id' => $customer->id,
            'type' => 'registro',
            'description' => 'Alta de cliente en el sistema OptiFiber.',
            'payload' => ['plan' => $customer->plan],
            'occurred_at' => now(),
        ]);

        return response()->json($customer, 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with([
            'zone',
            'port.napBox',
            'onu.olt',
            'events',
        ])->findOrFail($id);

        return response()->json([
            'info' => [
                'id' => $customer->id,
                'code' => $customer->code,
                'full_name' => $customer->full_name,
                'document' => $customer->document,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'latitude' => $customer->latitude,
                'longitude' => $customer->longitude,
                'plan' => $customer->plan,
                'status' => $customer->status->value,
                'installed_at' => $customer->installed_at?->format('Y-m-d H:i'),
            ],
            'connection' => $customer->port ? [
                'nap_id' => $customer->port->napBox->id,
                'nap_code' => $customer->port->napBox->code,
                'nap_name' => $customer->port->napBox->name,
                'address' => $customer->port->napBox->address,
                'port_number' => $customer->port->port_number,
                'total_ports' => $customer->port->napBox->port_capacity,
                'splitter_ratio' => $customer->port->napBox->splitter_ratio->value,
            ] : null,
            'equipment' => $customer->onu ? [
                'external_id' => $customer->onu->external_id,
                'sn' => $customer->onu->sn,
                'name' => $customer->onu->name,
                'olt_name' => $customer->onu->olt?->name,
                'board' => $customer->onu->board,
                'port' => $customer->onu->port,
                'onu_number' => $customer->onu->onu_number,
                'status' => $customer->onu->status,
                'signal_rx' => $customer->onu->signal_rx,
                'signal_tx' => $customer->onu->signal_tx,
                'distance_m' => $customer->onu->distance_m,
                'last_synced_at' => $customer->onu->last_synced_at?->toIso8601String(),
            ] : null,
            'events' => $customer->events,
        ]);
    }

    public function events(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        return response()->json($customer->events()->orderByDesc('occurred_at')->get());
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'plan' => 'nullable|string|max:50',
            'status' => ['nullable', Rule::enum(CustomerStatus::class)],
            'smartolt_onu_external_id' => 'nullable|string|max:100',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        if ($customer->nap_port_id) {
            NapPort::where('id', $customer->nap_port_id)->update(['status' => PortStatus::Free]);
        }

        $customer->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}
