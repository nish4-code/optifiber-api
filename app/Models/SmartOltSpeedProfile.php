<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartOltSpeedProfile extends Model
{
    use HasFactory;
    use HasUuid;

    protected $table = 'smartolt_speed_profiles';

    protected $fillable = [
        'id',
        'external_id',
        'name',
        'download_speed',
        'upload_speed',
    ];
}
