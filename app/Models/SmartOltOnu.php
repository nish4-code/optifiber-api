<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SmartOltOnu extends Model
{
    use HasFactory;

    protected $table = 'smartolt_onus';

    protected $fillable = [
        'external_id',
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
        'zone',
        'odb',
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

    protected function casts(): array
    {
        return [
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
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(SmartOltOlt::class, 'smartolt_olt_id');
    }

    public function zoneRecord(): BelongsTo
    {
        return $this->belongsTo(SmartOltZone::class, 'zone_id');
    }

    public function odbBox(): BelongsTo
    {
        return $this->belongsTo(SmartOltOdb::class, 'odb_id');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'smartolt_onu_external_id', 'external_id');
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(NetworkAlert::class, 'alertable');
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('status', 'Online');
    }
}
