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
            // Personal details
            'full_name'               => ['required', 'string', 'max:150'],
            'gender'                  => ['required', 'string', Rule::in(['male', 'female'])],
            'date_of_birth'           => ['required', 'date', 'before:today'],
            'nationality'             => ['required', 'string', 'size:2'],   // ISO 3166-1 alpha-2 (e.g. TZ, MA, EG)

            // Identity document
            'identity_type'           => ['required', 'string', Rule::in([
                AppConstant::IDENTITY_TYPE_NIC,
                AppConstant::IDENTITY_TYPE_RP,
                AppConstant::IDENTITY_TYPE_FN,
                AppConstant::IDENTITY_TYPE_SPECIAL_PASS,
            ])],
            'identity_number'         => ['required', 'string', 'max:100'],
            'identity_expiry_date'    => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.in'                     => 'Gender must be male or female.',
            'nationality.size'              => 'Nationality must be a 2-letter ISO country code (e.g. TZ, MA, EG).',
            'identity_type.in'              => 'Identity type must be one of: nic, rp, fn, special_pass.',
            'date_of_birth.before'          => 'Date of birth must be a past date.',
            'identity_expiry_date.after'    => 'Identity document must not be expired.',
        ];
    }
}
