<?php

namespace App\Models;

use App\Enums\NetworkStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrunkCable extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'code',
        'name',
        'fiber_count',
        'buffer_count',
        'strands_per_buffer',
        'path',
        'length_km',
        'map_color',
        'status',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'fiber_count' => 'integer',
            'buffer_count' => 'integer',
            'strands_per_buffer' => 'integer',
            'path' => 'array',
            'length_km' => 'float',
            'status' => NetworkStatus::class,
            'is_visible' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function buffers(): HasMany
    {
        return $this->hasMany(FiberBuffer::class)->orderBy('buffer_number');
    }

    public function strands(): HasMany
    {
        return $this->hasMany(FiberStrand::class)->orderBy('global_number');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', NetworkStatus::Active);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }
}
