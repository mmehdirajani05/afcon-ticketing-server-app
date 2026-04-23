<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'        => ['required', 'email'],
            'otp'          => ['required', 'string', 'digits:6'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
