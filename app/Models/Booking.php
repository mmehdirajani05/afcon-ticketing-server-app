<?php

namespace App\Models;

use App\Constants\AppConstant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'fan_id',
        'caf_ticket_ref',
        'match_id',
        'match_name',
        'match_date',
        'venue',
        'ticket_category',
        'seat_info',
        'amount',
        'currency',
        'payment_status',
        'transaction_id',
        'payment_metadata',
        'booking_status',
        'refund_status',
        'refund_transaction_id',
        'refund_requested_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'               => 'float',
            'payment_metadata'     => 'array',
            'refund_requested_at'  => 'datetime',
            'refunded_at'          => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === AppConstant::PAYMENT_STATUS_PAID;
    }

    public function isRefundable(): bool
    {
        return $this->isPaid()
            && $this->booking_status === AppConstant::BOOKING_STATUS_CONFIRMED
            && $this->refund_status === null;
    }
}
