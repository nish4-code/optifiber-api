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
        'board',
        'port',
        'onu_number',
        'zone',
        'odb',
        'status',
        'signal_rx',
        'signal_tx',
        'distance_m',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'onu_number' => 'integer',
            'signal_rx' => 'float',
            'signal_tx' => 'float',
            'distance_m' => 'integer',
            'last_synced_at' => 'datetime',
        ];
    }

    public function olt(): BelongsTo
    {
        return $this->belongsTo(SmartOltOlt::class, 'smartolt_olt_id');
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
