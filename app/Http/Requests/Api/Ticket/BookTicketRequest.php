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

            // Match details (provided by frontend)
            'match_name'       => ['required', 'string', 'max:255'],
            'match_date'       => ['required', 'string', 'max:100'], // ISO string from frontend
            'venue'            => ['required', 'string', 'max:255'],
            'match_city'       => ['required', 'string', 'max:255'],

            // Pricing (provided by frontend)
            'amount'           => ['required', 'numeric', 'min:0'],

            // For dev/testing only (optional overrides)
            'payment_status'   => ['nullable', 'in:pending,paid,failed'],
            'booking_status'   => ['nullable', 'in:pending,confirmed,cancelled,refunded'],
            'payment_metadata' => ['nullable', 'array'],
        ];
    }
}
