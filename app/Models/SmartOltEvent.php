<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartOltEvent extends Model
{
    use HasFactory;

    protected $table = 'smartolt_events';

    protected $fillable = [
        'event_type',
        'onu_sn',
        'olt_id',
        'severity',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
