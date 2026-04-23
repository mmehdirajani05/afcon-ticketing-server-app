<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Constants\AppConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ticket\RefundRequest;
use App\Models\Booking;
use App\Services\Payment\NMBPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(private NMBPaymentService $paymentService) {}

    /**
     * POST /user/bookings/{booking}/refund
     *
     * Refund flow:
     * 1. Validate ownership and refund eligibility
     * 2. Mark refund_requested
     * 3. Call CAF to release ticket
     * 4. Call NMB to process refund
     * 5. Update booking
     */
    public function request(RefundRequest $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        if (! $booking->isRefundable()) {
            return $this->error('This booking is not eligible for a refund.', 422);
        }

        // Mark as requested before processing (prevents duplicate requests)
        $booking->update([
            'refund_status'       => AppConstant::REFUND_STATUS_REQUESTED,
            'refund_requested_at' => now(),
        ]);

        try {
            $updated = $this->paymentService->processRefund($booking);
        } catch (\Throwable $e) {
            return $this->error('Refund processing failed: ' . $e->getMessage(), 500);
        }

        return $this->success('Refund processed successfully.', $updated);
    }

    /**
     * GET /user/bookings/{booking}/refund-status
     */
    public function status(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        return $this->success('Refund status.', [
            'booking_id'             => $booking->id,
            'refund_status'          => $booking->refund_status,
            'refund_transaction_id'  => $booking->refund_transaction_id,
            'refund_requested_at'    => $booking->refund_requested_at,
            'refunded_at'            => $booking->refunded_at,
        ]);
    }
}
