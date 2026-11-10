<?php

namespace App\Models;

use App\Enums\NetworkStatus;
use App\Enums\PortStatus;
use App\Enums\SplitterRatio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NapBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'code',
        'name',
        'splitter_ratio',
        'port_capacity',
        'address',
        'latitude',
        'longitude',
        'feeder_strand_id',
        'map_color',
        'map_icon',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'splitter_ratio' => SplitterRatio::class,
            'port_capacity' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => NetworkStatus::class,
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function feederStrand(): BelongsTo
    {
        return $this->belongsTo(FiberStrand::class, 'feeder_strand_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(NapPort::class)->orderBy('port_number');
    }

    public function splices(): HasMany
    {
        return $this->hasMany(FiberSplice::class);
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(NetworkAlert::class, 'alertable');
    }

    public function getOccupiedPortsCountAttribute(): int
    {
        return $this->ports()->where('status', PortStatus::Occupied)->count();
    }

    public function getFreePortsCountAttribute(): int
    {
        return $this->ports()->where('status', PortStatus::Free)->count();
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->port_capacity <= 0) {
            return 0.0;
        }

        return round(($this->occupied_ports_count / $this->port_capacity) * 100, 1);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', NetworkStatus::Active);
    }
}
