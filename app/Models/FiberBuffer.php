<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiberBuffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'trunk_cable_id',
        'buffer_number',
        'fiber_color_id',
    ];

    protected function casts(): array
    {
        return [
            'buffer_number' => 'integer',
        ];
    }

    public function trunkCable(): BelongsTo
    {
        return $this->belongsTo(TrunkCable::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(FiberColor::class, 'fiber_color_id');
    }

    public function strands(): HasMany
    {
        return $this->hasMany(FiberStrand::class)->orderBy('strand_number');
    }
}
