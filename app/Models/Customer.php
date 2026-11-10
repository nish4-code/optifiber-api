<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'code',
        'full_name',
        'document',
        'phone',
        'address',
        'latitude',
        'longitude',
        'plan',
        'nap_port_id',
        'smartolt_onu_external_id',
        'status',
        'installed_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => CustomerStatus::class,
            'installed_at' => 'datetime',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(NapPort::class, 'nap_port_id');
    }

    public function onu(): BelongsTo
    {
        return $this->belongsTo(SmartOltOnu::class, 'smartolt_onu_external_id', 'external_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CustomerEvent::class)->orderByDesc('occurred_at');
    }

    public function alerts(): MorphMany
    {
        return $this->morphMany(NetworkAlert::class, 'alertable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active);
    }
}
