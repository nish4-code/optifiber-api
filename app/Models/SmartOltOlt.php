<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartOltOlt extends Model
{
    use HasFactory;

    protected $table = 'smartolt_olts';

    protected $fillable = [
        'external_id',
        'name',
        'ip',
        'hardware_version',
        'status',
        'metrics',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function onus(): HasMany
    {
        return $this->hasMany(SmartOltOnu::class);
    }
}
