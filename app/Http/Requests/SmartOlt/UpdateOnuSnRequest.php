<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuSnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sn' => ['required', 'string', 'size:12'],
        ];
    }
}
