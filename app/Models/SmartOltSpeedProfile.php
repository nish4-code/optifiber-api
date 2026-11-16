<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartOltSpeedProfile extends Model
{
    use HasFactory;

    protected $table = 'smartolt_speed_profiles';

    protected $fillable = [
        'external_id',
        'name',
        'download_speed',
        'upload_speed',
    ];
}
