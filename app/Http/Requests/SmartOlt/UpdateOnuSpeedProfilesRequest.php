<?php

declare(strict_types=1);

namespace App\Http\Requests\SmartOlt;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOnuSpeedProfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload_speed_profile_name' => ['required', 'string', 'max:150'],
            'download_speed_profile_name' => ['required', 'string', 'max:150'],
        ];
    }
}
