<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartOltOlt extends Model
{
    use HasFactory;

    protected $table = 'smartolt_olts';

    protected $fillable = [
        'external_id',
        'name',
        'ip',
        'telnet_port',
        'snmp_port',
        'hardware_version',
        'status',
        'latitude',
        'longitude',
        'metrics',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'telnet_port' => 'integer',
            'snmp_port' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'metrics' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function onus(): HasMany
    {
        return $this->hasMany(SmartOltOnu::class, 'smartolt_olt_id');
    }

    public function odbs(): HasMany
    {
        return $this->hasMany(SmartOltOdb::class, 'smartolt_olt_id');
    }
}
