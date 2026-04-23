<?php

namespace App\Services\Payment;

use App\Constants\AppConstant;
use App\Models\Booking;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Ticket\CAFTicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * NMBPaymentService — real-time payment processing via NMB Bank API.
 *
 * Flow:
 *   1. initiatePayment()  → creates PENDING booking, calls NMB to get payment URL
 *   2. handleCallback()   → NMB posts result to our webhook
 *   3. confirmSuccess()   → confirm with CAF, update booking to CONFIRMED
 *   4. handleFailure()    → mark booking FAILED
 */
class NMBPaymentService
{
    private string $baseUrl;
    private string $merchantId;
    private string $secretKey;
    private int    $timeout;

    public function __construct(
        private CAFTicketService $cafService,
        private FirebaseNotificationService $notificationService,
    ) {
        $this->baseUrl    = rtrim(config('services.nmb.url', ''), '/');
        $this->merchantId = config('services.nmb.merchant_id', '');
        $this->secretKey  = config('services.nmb.secret_key', '');
        $this->timeout    = (int) config('services.nmb.timeout', 15);
    }

    /**
     * Step 1: Initiate payment.
     * Creates a PENDING booking and returns a payment URL for the client to redirect/open.
     */
    public function initiate(Booking $booking): array
    {
        $reference = $this->generateReference($booking);

        $payload = [
            'merchant_id'    => $this->merchantId,
            'reference'      => $reference,
            'amount'         => number_format($booking->amount, 2, '.', ''),
            'currency'       => $booking->currency,
            'description'    => 'AFCON 2027 Ticket — ' . $booking->match_name,
            'callback_url'   => config('app.url') . '/api/payments/nmb/callback',
            'redirect_url'   => config('services.nmb.redirect_url', config('app.url')),
            'customer_name'  => $booking->user->name,
            'customer_email' => $booking->user->email,
            'booking_id'     => $booking->id,
            'checksum'       => $this->generateChecksum($reference, $booking->amount),
        ];

        if (! $this->baseUrl) {
            // Mock response for development
            $booking->update(['transaction_id' => $reference]);

            return [
                'payment_url'  => 'https://mock-nmb.test/pay/' . $reference,
                'reference'    => $reference,
                'expires_at'   => now()->addMinutes(30)->toIso8601String(),
                'mock'         => true,
            ];
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout($this->timeout)
                ->post($this->baseUrl . '/payments/initiate', $payload);

            if ($response->failed()) {
                throw new \RuntimeException('NMB payment initiation failed: ' . $response->body());
            }

            $data = $response->json();

            $booking->update(['transaction_id' => $reference]);

            return [
                'payment_url' => $data['payment_url'],
                'reference'   => $reference,
                'expires_at'  => $data['expires_at'] ?? now()->addMinutes(30)->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            Log::error('NMBPaymentService initiate error', ['booking' => $booking->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Step 2/3: Handle success callback from NMB.
     * Verifies checksum, confirms ticket with CAF, updates booking.
     */
    public function handleSuccess(array $callbackData): Booking
    {
        $booking = Booking::where('transaction_id', $callbackData['reference'] ?? '')->firstOrFail();

        // Prevent duplicate processing
        if ($booking->payment_status === AppConstant::PAYMENT_STATUS_PAID) {
            return $booking;
        }

        $this->verifyCallbackChecksum($callbackData);

        return DB::transaction(function () use ($booking, $callbackData) {
            $booking->update([
                'payment_status'   => AppConstant::PAYMENT_STATUS_PAID,
                'payment_metadata' => $callbackData,
            ]);

            // Confirm with CAF in real-time
            try {
                $this->cafService->confirmTicket($booking->caf_ticket_ref, $booking->transaction_id);

                $booking->update(['booking_status' => AppConstant::BOOKING_STATUS_CONFIRMED]);
            } catch (\Throwable $e) {
                Log::error('CAF confirmation failed after payment', [
                    'booking' => $booking->id,
                    'error'   => $e->getMessage(),
                ]);
                // Keep booking in PENDING status for manual review; payment is still captured
            }

            $this->notificationService->send(
                $booking->user,
                'Booking Confirmed!',
                'Your ticket for ' . $booking->match_name . ' has been confirmed.',
                ['type' => 'booking_confirmed', 'booking_id' => $booking->id]
            );

            return $booking->fresh();
        });
    }

    /**
     * Handle failure/cancellation callback from NMB.
     * Release the ticket hold on CAF.
     */
    public function handleFailure(array $callbackData): Booking
    {
        $booking = Booking::where('transaction_id', $callbackData['reference'] ?? '')->firstOrFail();

        if ($booking->payment_status !== AppConstant::PAYMENT_STATUS_PENDING) {
            return $booking;
        }

        $booking->update([
            'payment_status'   => AppConstant::PAYMENT_STATUS_FAILED,
            'booking_status'   => AppConstant::BOOKING_STATUS_CANCELLED,
            'payment_metadata' => $callbackData,
        ]);

        // Release ticket hold on CAF silently
        try {
            if ($booking->caf_ticket_ref) {
                $this->cafService->cancelTicket($booking->caf_ticket_ref, 'payment_failed');
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to release CAF hold after payment failure', [
                'booking' => $booking->id,
                'error'   => $e->getMessage(),
            ]);
        }

        $this->notificationService->send(
            $booking->user,
            'Payment Failed',
            'Payment for your ticket could not be processed. Please try again.',
            ['type' => 'payment_failed', 'booking_id' => $booking->id]
        );

        return $booking->fresh();
    }

    /**
     * Process a refund: release ticket from CAF, then refund via NMB.
     */
    public function processRefund(Booking $booking): Booking
    {
        $booking->update([
            'refund_status'       => AppConstant::REFUND_STATUS_PROCESSING,
            'refund_requested_at' => $booking->refund_requested_at ?? now(),
        ]);

        return DB::transaction(function () use ($booking) {
            // Step 1: Release from CAF
            try {
                $this->cafService->cancelTicket($booking->caf_ticket_ref, 'refund_requested');
            } catch (\Throwable $e) {
                Log::error('CAF ticket release failed during refund', [
                    'booking' => $booking->id,
                    'error'   => $e->getMessage(),
                ]);
                $booking->update(['refund_status' => AppConstant::REFUND_STATUS_FAILED]);
                throw $e;
            }

            // Step 2: Trigger NMB refund
            $refundRef = null;

            if ($this->baseUrl && $booking->transaction_id) {
                try {
                    $response = Http::withHeaders($this->headers())
                        ->timeout($this->timeout)
                        ->post($this->baseUrl . '/payments/refund', [
                            'merchant_id'    => $this->merchantId,
                            'transaction_id' => $booking->transaction_id,
                            'amount'         => $booking->amount,
                            'reason'         => 'customer_refund_request',
                        ]);

                    if ($response->successful()) {
                        $refundRef = $response->json('refund_transaction_id');
                    }
                } catch (\Throwable $e) {
                    Log::error('NMB refund API error', ['booking' => $booking->id, 'error' => $e->getMessage()]);
                }
            } else {
                $refundRef = 'MOCK-REFUND-' . strtoupper(bin2hex(random_bytes(4)));
            }

            $booking->update([
                'refund_status'          => AppConstant::REFUND_STATUS_REFUNDED,
                'refund_transaction_id'  => $refundRef,
                'refunded_at'            => now(),
                'booking_status'         => AppConstant::BOOKING_STATUS_REFUNDED,
                'payment_status'         => AppConstant::PAYMENT_STATUS_FAILED,
            ]);

            $this->notificationService->send(
                $booking->user,
                'Refund Processed',
                'Your refund of ' . $booking->currency . ' ' . number_format($booking->amount, 2) . ' has been processed.',
                ['type' => 'refund_processed', 'booking_id' => $booking->id]
            );

            return $booking->fresh();
        });
    }

    private function generateReference(Booking $booking): string
    {
        return 'AFCON-' . $booking->id . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    private function generateChecksum(string $reference, float $amount): string
    {
        return hash_hmac('sha256', $reference . '|' . number_format($amount, 2), $this->secretKey);
    }

    private function verifyCallbackChecksum(array $data): void
    {
        if (! $this->secretKey) {
            return; // Skip in dev/mock mode
        }

        $expected = hash_hmac(
            'sha256',
            ($data['reference'] ?? '') . '|' . ($data['amount'] ?? ''),
            $this->secretKey
        );

        if (! hash_equals($expected, $data['checksum'] ?? '')) {
            throw new \RuntimeException('Invalid NMB callback checksum.', 400);
        }
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }
}
