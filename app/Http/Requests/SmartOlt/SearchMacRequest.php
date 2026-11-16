<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class SearchMacRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mac_address' => ['required', 'string'],
            'olt_id' => ['nullable', 'string'],
        ];
    }
}
