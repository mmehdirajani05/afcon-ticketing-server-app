<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['sometimes', 'string', 'max:255'],
            // ignore current user's own phone when checking uniqueness
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', 'unique:users,phone,' . auth()->id()],
        ];
    }
}
