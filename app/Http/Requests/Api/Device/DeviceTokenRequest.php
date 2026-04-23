<?php

namespace App\Http\Requests\Api\Device;

use App\Constants\AppConstant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required', 'string', 'min:10', 'max:255'],
            'platform' => ['required', 'string', Rule::in([
                AppConstant::PLATFORM_ANDROID,
                AppConstant::PLATFORM_IOS,
                AppConstant::PLATFORM_WEB,
            ])],
        ];
    }
}
