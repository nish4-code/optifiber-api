<?php

namespace App\Models;

use App\Enums\StrandStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiberStrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'trunk_cable_id',
        'fiber_buffer_id',
        'strand_number',
        'global_number',
        'fiber_color_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'strand_number' => 'integer',
            'global_number' => 'integer',
            'status' => StrandStatus::class,
        ];
    }

    public function trunkCable(): BelongsTo
    {
        return $this->belongsTo(TrunkCable::class);
    }

    public function buffer(): BelongsTo
    {
        return $this->belongsTo(FiberBuffer::class, 'fiber_buffer_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(FiberColor::class, 'fiber_color_id');
    }

    public function splicesAsIn(): HasMany
    {
        return $this->hasMany(FiberSplice::class, 'strand_in_id');
    }

    public function splicesAsOut(): HasMany
    {
        return $this->hasMany(FiberSplice::class, 'strand_out_id');
    }

    public function fedNaps(): HasMany
    {
        return $this->hasMany(NapBox::class, 'feeder_strand_id');
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('status', StrandStatus::Free);
    }
}
