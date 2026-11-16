<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOdbOrCabinetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'zone_id' => ['sometimes', 'string'],
            'total_ports' => ['sometimes', 'integer', 'min:1', 'max:128'],
        ];
    }
}
