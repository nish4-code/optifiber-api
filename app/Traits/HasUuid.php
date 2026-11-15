<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

trait HasUuid
{
    use HasUuids;

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    public static function generateUuid36(): string
    {
        return (string) Str::uuid();
    }
}
