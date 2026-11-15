<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartOltOdb extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_odbs';

    protected $fillable = [
        'id',
        'external_id',
        'name',
        'zone_id',
        'smartolt_olt_id',
        'pon_port',
        'total_ports',
        'used_ports',
    ];

    protected $casts = [
        'total_ports' => 'integer',
        'used_ports' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SmartOltZone::class, 'zone_id');
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(SmartOltOlt::class, 'smartolt_olt_id');
    }

    public function onus(): HasMany
    {
        return $this->hasMany(SmartOltOnu::class, 'odb_id');
    }
}
