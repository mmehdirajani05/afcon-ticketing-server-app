<?php

namespace App\Services\Ticket;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CAFTicketService — real-time integration with the CAF ticketing platform.
 *
 * All calls are synchronous (no queues). If CAF API is unavailable, exceptions
 * bubble up so the caller can handle them appropriately.
 */
class CAFTicketService
{
    private string $baseUrl;
    private string $apiKey;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.caf.url', ''), '/');
        $this->apiKey  = config('services.caf.key', '');
        $this->timeout = (int) config('services.caf.timeout', 15);
    }

    /**
     * Verify that a Fan ID is valid and eligible to purchase tickets.
     */
    public function verifyFanId(string $fanId): array
    {
        return $this->post('/fan-id/verify', ['fan_id' => $fanId]);
    }

    /**
     * Fetch upcoming matches with optional filters.
     */
    public function getMatches(array $filters = []): array
    {
        $params = array_filter([
            'team_a'    => $filters['team_a'] ?? null,
            'team_b'    => $filters['team_b'] ?? null,
            'stadium'   => $filters['stadium'] ?? null,
            'city'      => $filters['city'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to'   => $filters['date_to'] ?? null,
            'page'      => $filters['page'] ?? 1,
            'per_page'  => $filters['per_page'] ?? 20,
        ]);

        return $this->get('/matches', $params);
    }

    /**
     * Get tickets for a specific match with availability and pricing.
     */
    public function getMatchTickets(string $matchId, array $filters = []): array
    {
        $params = array_filter([
            'category'   => $filters['category'] ?? null,
            'price_min'  => $filters['price_min'] ?? null,
            'price_max'  => $filters['price_max'] ?? null,
        ]);

        return $this->get('/matches/' . $matchId . '/tickets', $params);
    }

    /**
     * Hold/book a ticket seat on CAF's side.
     * Returns CAF ticket reference on success.
     */
    public function bookTicket(string $fanId, string $matchId, string $category, array $options = []): array
    {
        return $this->post('/tickets/book', [
            'fan_id'   => $fanId,
            'match_id' => $matchId,
            'category' => $category,
            'seat'     => $options['seat'] ?? null,
        ]);
    }

    /**
     * Confirm a booked ticket after successful payment.
     */
    public function confirmTicket(string $cafTicketRef, string $transactionId): array
    {
        return $this->post('/tickets/' . $cafTicketRef . '/confirm', [
            'transaction_id' => $transactionId,
        ]);
    }

    /**
     * Cancel/release a ticket back to inventory.
     */
    public function cancelTicket(string $cafTicketRef, string $reason = 'user_request'): array
    {
        return $this->post('/tickets/' . $cafTicketRef . '/cancel', [
            'reason' => $reason,
        ]);
    }

    /**
     * Fetch all tickets owned by a Fan ID.
     */
    public function getFanTickets(string $fanId): array
    {
        return $this->get('/fan-id/' . $fanId . '/tickets');
    }

    // ─── HTTP helpers ─────────────────────────────────────────────────────────

    private function get(string $path, array $params = []): array
    {
        if (! $this->baseUrl) {
            return $this->mockResponse($path, $params);
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout($this->timeout)
                ->get($this->baseUrl . $path, $params);

            return $this->parseResponse($response, $path);
        } catch (\Throwable $e) {
            Log::error('CAFTicketService GET error', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function post(string $path, array $body = []): array
    {
        if (! $this->baseUrl) {
            return $this->mockResponse($path, $body);
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout($this->timeout)
                ->post($this->baseUrl . $path, $body);

            return $this->parseResponse($response, $path);
        } catch (\Throwable $e) {
            Log::error('CAFTicketService POST error', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'X-App-Source'  => 'afcon-ticketing',
        ];
    }

    private function parseResponse(\Illuminate\Http\Client\Response $response, string $path): array
    {
        if ($response->failed()) {
            $error = $response->json('message') ?? 'CAF API error (' . $response->status() . ')';
            Log::warning('CAF API non-2xx response', ['path' => $path, 'status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException($error, $response->status());
        }

        return $response->json();
    }

    /**
     * Mock responses for development / when CAF API is not configured.
     */
    private function mockResponse(string $path, array $params): array
    {
        Log::info('CAFTicketService: using mock response', ['path' => $path]);

        if (str_contains($path, '/fan-id/verify')) {
            return ['verified' => true, 'fan_id' => $params['fan_id'] ?? 'MOCK'];
        }

        if (str_contains($path, '/matches') && ! str_contains($path, '/tickets')) {
            return [
                'data' => [
                    [
                        'match_id'  => 'AFCON27-M001',
                        'home_team' => 'Morocco',
                        'away_team' => 'Egypt',
                        'date'      => '2027-06-15T18:00:00Z',
                        'venue'     => 'Benjamin Mkapa Stadium',
                        'city'      => 'Dar es Salaam',
                    ],
                ],
                'total' => 1, 'page' => 1, 'per_page' => 20,
            ];
        }

        if (str_contains($path, '/tickets/book')) {
            return [
                'caf_ticket_ref' => 'CAF-' . strtoupper(bin2hex(random_bytes(4))),
                'status'         => 'held',
                'hold_expires'   => now()->addMinutes(15)->toIso8601String(),
            ];
        }

        if (str_contains($path, '/confirm')) {
            return ['status' => 'confirmed', 'caf_ticket_ref' => last(explode('/', $path))];
        }

        if (str_contains($path, '/cancel')) {
            return ['status' => 'cancelled'];
        }

        return ['data' => [], 'mock' => true];
    }
}
