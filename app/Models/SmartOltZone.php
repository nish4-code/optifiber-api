<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartOltZone extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_zones';

    protected $fillable = [
        'id',
        'external_id',
        'name',
        'description',
    ];

    public function odbs(): HasMany
    {
        return $this->hasMany(SmartOltOdb::class, 'zone_id');
    }

    public function onus(): HasMany
    {
        return $this->hasMany(SmartOltOnu::class, 'zone_id');
    }
}
