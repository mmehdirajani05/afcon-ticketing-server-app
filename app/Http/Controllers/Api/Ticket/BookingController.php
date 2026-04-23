<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Constants\AppConstant;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Payment\NMBPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private NMBPaymentService $paymentService) {}

    /**
     * GET /user/bookings
     * All bookings for the authenticated user with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->booking_status) {
            $query->where('booking_status', $request->booking_status);
        }

        $bookings = $query->paginate($request->integer('per_page', 15));

        return $this->success('Bookings fetched.', $bookings);
    }

    /**
     * GET /user/bookings/{booking}
     * Single booking detail.
     */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        return $this->success('Booking fetched.', $booking);
    }

    /**
     * POST /payments/nmb/callback
     * NMB payment gateway callback (public endpoint — verified via checksum).
     */
    public function nmbCallback(Request $request): JsonResponse
    {
        $data   = $request->all();
        $status = $data['status'] ?? 'failed';

        try {
            if ($status === 'success' || $status === 'paid') {
                $booking = $this->paymentService->handleSuccess($data);
            } else {
                $booking = $this->paymentService->handleFailure($data);
            }

            return response()->json(['status' => 'ok', 'booking_status' => $booking->booking_status]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * GET /user/revenue (admin/analytics endpoint placeholder)
     * Revenue summary via aggregate queries — no separate table needed.
     */
    public function revenue(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $summary = Booking::where('user_id', $userId)
            ->selectRaw("
                COUNT(*) as total_bookings,
                SUM(CASE WHEN payment_status = ? THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN refund_status = ? THEN amount ELSE 0 END) as total_refunded
            ", [AppConstant::PAYMENT_STATUS_PAID, AppConstant::REFUND_STATUS_REFUNDED])
            ->first();

        return $this->success('Revenue summary.', $summary);
    }
}
