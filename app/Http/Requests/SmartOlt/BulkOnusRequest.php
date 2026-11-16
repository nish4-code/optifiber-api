<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class BulkOnusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'onus' => ['required', 'array', 'min:1'],
            'onus.*' => ['required', 'string'],
        ];
    }
}
