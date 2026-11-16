<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class StoreVlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vlan_id' => ['required', 'integer', 'min:1', 'max:4094'],
            'description' => ['nullable', 'string', 'max:100'],
        ];
    }
}
