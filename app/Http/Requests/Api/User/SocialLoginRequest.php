<?php

namespace App\Http\Requests\Api\User;

use App\Constants\AppConstant;
use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:' . AppConstant::SOCIAL_PROVIDER_GOOGLE . ',' . AppConstant::SOCIAL_PROVIDER_APPLE],
            'id_token'  => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.in' => 'Supported providers are: google, apple.',
        ];
    }
}
