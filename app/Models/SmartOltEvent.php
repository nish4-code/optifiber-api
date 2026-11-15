<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartOltEvent extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_events';

    protected $fillable = [
        'id',
        'event_type',
        'onu_sn',
        'olt_id',
        'severity',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
