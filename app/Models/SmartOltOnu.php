<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartOltOnu extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_onus';

    protected $fillable = [
        'id',
        'unique_external_id',
        'sn',
        'name',
        'smartolt_olt_id',
        'zone_id',
        'odb_id',
        'pon_type',
        'board',
        'port',
        'onu_number',
        'onu_type_id',
        'onu_type_name',
        'status',
        'administrative_status',
        'catv_status',
        'signal_value',
        'signal_1310',
        'signal_1490',
        'signal_rx',
        'signal_tx',
        'distance_m',
        'mode',
        'vlan',
        'address',
        'latitude',
        'longitude',
        'extra_data',
        'last_synced_at',
    ];

    protected $casts = [
        'onu_number' => 'integer',
        'signal_rx' => 'float',
        'signal_tx' => 'float',
        'distance_m' => 'integer',
        'vlan' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'extra_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(SmartOltOlt::class, 'smartolt_olt_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SmartOltZone::class, 'zone_id');
    }

    public function odb(): BelongsTo
    {
        return $this->belongsTo(SmartOltOdb::class, 'odb_id');
    }
}
