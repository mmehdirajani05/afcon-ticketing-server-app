<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ticket\BookTicketRequest;
use App\Http\Requests\Api\Ticket\TicketFilterRequest;
use App\Models\Booking;
use App\Services\Fan\FanIdService;
use App\Services\Payment\NMBPaymentService;
use App\Services\Ticket\CAFTicketService;
use App\Services\Ticket\DigitalTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private CAFTicketService    $cafService,
        private NMBPaymentService   $paymentService,
        private FanIdService        $fanIdService,
        private DigitalTicketService $digitalTicketService,
    ) {}

    /**
     * GET /tickets
     * Search matches and available tickets with filters.
     */
    public function index(TicketFilterRequest $request): JsonResponse
    {
        $filters = array_filter($request->validated());
        $matches = $this->cafService->getMatches($filters);

        // If a specific match_id is given, also fetch its ticket availability
        if ($request->match_id) {
            $tickets = $this->cafService->getMatchTickets($request->match_id, $filters);

            return $this->success('Tickets fetched.', [
                'match'   => $matches,
                'tickets' => $tickets,
            ]);
        }

        return $this->success('Matches fetched.', $matches);
    }

    /**
     * GET /tickets/{matchId}
     * Get tickets for a specific match.
     */
    public function matchTickets(TicketFilterRequest $request, string $matchId): JsonResponse
    {
        $tickets = $this->cafService->getMatchTickets($matchId, $request->validated());

        return $this->success('Tickets fetched.', $tickets);
    }

    /**
     * POST /tickets/book
     *
     * Full booking flow:
     * 1. Verify Fan ID with CAF
     * 2. Hold ticket on CAF
     * 3. Create PENDING booking
     * 4. Initiate NMB payment
     * 5. Return payment URL to client
     */
    public function book(BookTicketRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->fan_id) {
            return $this->error('You must have a verified Fan ID to purchase tickets.', 403);
        }

        // Verify Fan ID eligibility with CAF
        $cafVerification = $this->cafService->verifyFanId($user->fan_id);

        if (! ($cafVerification['verified'] ?? false)) {
            return $this->error('Your Fan ID could not be verified with CAF. ' . ($cafVerification['reason'] ?? ''), 403);
        }

        // Fetch match details for storing in booking
        $matchData = $this->cafService->getMatches(['match_id' => $request->match_id]);
        $match     = $matchData['data'][0] ?? [];

        // Hold the ticket on CAF
        $cafBooking = $this->cafService->bookTicket(
            $user->fan_id,
            $request->match_id,
            $request->ticket_category,
            ['seat' => $request->seat_info]
        );

        // Create PENDING booking
        $booking = Booking::create([
            'user_id'          => $user->id,
            'fan_id'           => $user->fan_id,
            'caf_ticket_ref'   => $cafBooking['caf_ticket_ref'],
            'match_id'         => $request->match_id,
            'match_name'       => ($match['home_team'] ?? '') . ' vs ' . ($match['away_team'] ?? ''),
            'match_date'       => $match['date'] ?? null,
            'venue'            => $match['venue'] ?? null,
            'ticket_category'  => $request->ticket_category,
            'seat_info'        => $request->seat_info,
            'amount'           => $match['price'] ?? 0,
            'currency'         => $match['currency'] ?? 'TZS',
            'payment_status'   => 'pending',
            'booking_status'   => 'pending',
        ]);

        // Initiate NMB payment
        $paymentData = $this->paymentService->initiate($booking);

        return $this->success('Booking created. Complete payment to confirm.', [
            'booking'     => $booking->fresh(),
            'payment_url' => $paymentData['payment_url'],
            'reference'   => $paymentData['reference'],
            'expires_at'  => $paymentData['expires_at'],
        ], 201);
    }

    /**
     * GET /user/tickets
     * List all bookings for the authenticated user.
     */
    public function myTickets(Request $request): JsonResponse
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return $this->success('Tickets fetched.', $bookings);
    }

    /**
     * GET /user/tickets/{booking}/download
     * Download digital PDF ticket.
     */
    public function download(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        if (! $booking->isPaid()) {
            return $this->error('Ticket is not confirmed yet.', 403);
        }

        return $this->digitalTicketService->stream($booking);
    }
}
