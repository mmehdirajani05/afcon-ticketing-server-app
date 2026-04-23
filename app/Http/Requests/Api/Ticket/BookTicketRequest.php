<?php

namespace App\Http\Requests\Api\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class BookTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'match_id'         => ['required', 'string'],
            'ticket_category'  => ['required', 'string', 'max:50'],
            'seat_info'        => ['nullable', 'string', 'max:100'],
        ];
    }
}
