<?php

namespace App\Models;

use App\Enums\PortStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NapPort extends Model
{
    use HasFactory;

    protected $fillable = [
        'nap_box_id',
        'port_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'port_number' => 'integer',
            'status' => PortStatus::class,
        ];
    }

    public function napBox(): BelongsTo
    {
        return $this->belongsTo(NapBox::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }
}
