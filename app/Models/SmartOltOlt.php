<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartOltOlt extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_olts';

    protected $fillable = [
        'id',
        'external_id',
        'name',
        'olt_hardware_version',
        'ip',
        'telnet_port',
        'snmp_port',
        'status',
        'latitude',
        'longitude',
        'metrics',
        'last_synced_at',
    ];

    protected $casts = [
        'telnet_port' => 'integer',
        'snmp_port' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'metrics' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function onus(): HasMany
    {
        return $this->hasMany(SmartOltOnu::class, 'smartolt_olt_id');
    }

    public function odbs(): HasMany
    {
        return $this->hasMany(SmartOltOdb::class, 'smartolt_olt_id');
    }
}
