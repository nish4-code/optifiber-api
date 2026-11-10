<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'center_lat',
        'center_lng',
    ];

    protected function casts(): array
    {
        return [
            'center_lat' => 'decimal:7',
            'center_lng' => 'decimal:7',
        ];
    }

    public function trunkCables(): HasMany
    {
        return $this->hasMany(TrunkCable::class);
    }

    public function spliceClosures(): HasMany
    {
        return $this->hasMany(SpliceClosure::class);
    }

    public function napBoxes(): HasMany
    {
        return $this->hasMany(NapBox::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
