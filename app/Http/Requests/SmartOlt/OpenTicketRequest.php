<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class OpenTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
