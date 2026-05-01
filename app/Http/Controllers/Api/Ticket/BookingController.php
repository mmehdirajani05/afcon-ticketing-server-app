<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Constants\AppConstant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ticket\BookTicketRequest;
use App\Models\Booking;
use App\Services\Payment\NMBPaymentService;
use App\Services\Ticket\CAFTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function __construct(
        private NMBPaymentService $paymentService,
        private CAFTicketService $cafService,
    ) {}

    /**
     * POST /user/bookings
     * Create a booking for the authenticated user and initiate payment.
     */
    public function store(BookTicketRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        if (! $user->fan_id) {
            return $this->error('You must have a verified Fan ID to purchase tickets.', 403);
        }

        $cafVerification = $this->cafService->verifyFanId($user->fan_id);

        if (! ($cafVerification['verified'] ?? false)) {
            return $this->error('Your Fan ID could not be verified with CAF. ' . ($cafVerification['reason'] ?? ''), 403);
        }

        $cafBooking = $this->cafService->bookTicket(
            $user->fan_id,
            $payload['match_id'],
            $payload['ticket_category'],
            ['seat' => $payload['seat_info'] ?? null]
        );

        $booking = Booking::create([
            'user_id'          => $user->id,
            'fan_id'           => $user->fan_id,
            'caf_ticket_ref'   => $cafBooking['caf_ticket_ref'],
            'caf_booking_payload' => $cafBooking,
            'match_id'         => $payload['match_id'],
            'match_name'       => $payload['match_name'],
            'match_date'       => $payload['match_date'],
            'venue'            => $payload['venue'],
            'match_city'       => $payload['match_city'],
            'ticket_category'  => $payload['ticket_category'],
            'seat_info'        => $payload['seat_info'] ?? null,
            'amount'           => $payload['amount'],
            'payment_status'   => $payload['payment_status'] ?? AppConstant::PAYMENT_STATUS_PENDING,
            'booking_status'   => $payload['booking_status'] ?? AppConstant::BOOKING_STATUS_PENDING,
            'payment_metadata' => $payload['payment_metadata'] ?? null,
            'booked_at'        => now(),
        ]);

        $paymentData = $this->paymentService->initiate($booking);

        return $this->success('Booking created. Complete payment to confirm.', [
            'booking'     => $booking->fresh(),
            'payment_url' => $paymentData['payment_url'],
            'reference'   => $paymentData['reference'],
            'expires_at'  => $paymentData['expires_at'],
        ], 201);
    }

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
