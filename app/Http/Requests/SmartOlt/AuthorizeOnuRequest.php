<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeOnuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'olt_id' => ['required', 'string'],
            'board' => ['required', 'string'],
            'port' => ['required', 'string'],
            'sn' => ['required', 'string', 'size:12'],
            'onu_type_id' => ['required', 'string'],
            'zone_id' => ['nullable', 'string'],
            'odb_id' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:150'],
            'vlan' => ['nullable', 'integer', 'min:1', 'max:4094'],
            'onu_mode' => ['nullable', 'string', 'in:Routing,Bridging'],
        ];
    }
}

