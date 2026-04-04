<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'global_role'           => ['required', 'string', 'in:customer,partner,admin'],
            'registration_source'   => ['nullable', 'string', 'max:50'],
        ];
    }
}
