<?php

namespace App\Http\Requests\Api\Fan;

use App\Constants\AppConstant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFanIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'       => ['required', 'string', 'max:150'],
            'identity_type'   => ['required', 'string', Rule::in([
                AppConstant::IDENTITY_TYPE_NIC,
                AppConstant::IDENTITY_TYPE_PERMIT,
                AppConstant::IDENTITY_TYPE_SPECIAL_PASS,
                AppConstant::IDENTITY_TYPE_VISA,
            ])],
            'identity_number' => ['required', 'string', 'max:100'],
            'nationality'     => ['nullable', 'string', 'size:2'], // ISO 3166-1 alpha-2
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'identity_type.in' => 'Identity type must be one of: nic, permit, special_pass, visa.',
            'nationality.size' => 'Nationality must be a 2-letter ISO country code (e.g. TZ, MA).',
        ];
    }
}
