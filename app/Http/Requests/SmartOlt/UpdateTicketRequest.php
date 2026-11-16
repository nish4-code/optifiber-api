<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'string', 'max:100'],
            'notes' => ['required', 'string'],
        ];
    }
}
