<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiberColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'name',
        'tia_name',
        'hex',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function buffers(): HasMany
    {
        return $this->hasMany(FiberBuffer::class);
    }

    public function strands(): HasMany
    {
        return $this->hasMany(FiberStrand::class);
    }
}
