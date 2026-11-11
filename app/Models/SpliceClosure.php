<?php

namespace App\Models;

use App\Enums\ClosureType;
use App\Enums\MountType;
use App\Enums\NetworkStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SpliceClosure extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'code',
        'name',
        'type',
        'mount_type',
        'fiber_capacity',
        'latitude',
        'longitude',
        'reserve_meters',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ClosureType::class,
            'mount_type' => MountType::class,
            'fiber_capacity' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'reserve_meters' => 'float',
            'status' => NetworkStatus::class,
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function splices(): HasMany
    {
        return $this->hasMany(FiberSplice::class);
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(NetworkAlert::class, 'alertable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', NetworkStatus::Active);
    }
}
