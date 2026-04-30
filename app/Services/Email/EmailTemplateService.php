<?php

namespace App\Services\Email;

use App\Constants\AppConstant;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Sends application emails using file-based Blade views.
 *
 * Architecture:
 *   resources/views/emails/layout.blade.php  — shared branded wrapper (header, footer)
 *   resources/views/emails/<type>.blade.php  — only the unique inner content per email type
 *
 * No database involved, no cache needed.
 * Each send() call renders the view to an HTML string and dispatches it
 * to the SendEmailJob queue — the HTTP response is returned immediately.
 *
 * Adding a new email type:
 *   1. Create resources/views/emails/your_type.blade.php extending emails.layout
 *   2. Add a typed helper method below (sendYourType)
 *   3. That's it.
 */
class EmailTemplateService
{
    /**
     * Render a Blade email view and dispatch it to the queue.
     *
     * @param  string  $view       Blade view name, e.g. 'emails.otp'
     * @param  string  $subject    Email subject line
     * @param  string  $toEmail    Recipient email address
     * @param  string  $toName     Recipient display name
     * @param  array   $data       Variables passed to the blade view
     */
    public function send(string $view, string $subject, string $toEmail, string $toName, array $data = []): void
    {
        try {
            $html = view($view, array_merge(['app' => config('app.name')], $data))->render();

            SendEmailJob::dispatch($toEmail, $toName, $subject, $html);
        } catch (\Throwable $e) {
            Log::error('EmailTemplateService: failed to render or dispatch email', [
                'view'  => $view,
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // ── Typed helpers — one per email type ────────────────────────────────────

    public function sendOtp(User $user, string $code, string $type): void
    {
        $expiry = config('otp.expiry_minutes', 10);

        // Password reset gets its own template (orange accent, different wording)
        if ($type === AppConstant::OTP_TYPE_PASSWORD_RESET) {
            $this->send(
                view: 'emails.reset_password',
                subject: config('app.name') . ' — Password Reset Request',
                toEmail: $user->email,
                toName: $user->name,
                data: [
                    'name'   => $user->name,
                    'otp'    => $code,
                    'expiry' => $expiry,
                ]
            );

            return;
        }

        // Email verification and any other OTP type
        $this->send(
            view: 'emails.otp',
            subject: config('app.name') . ' — Your Verification Code: ' . $code,
            toEmail: $user->email,
            toName: $user->name,
            data: [
                'otp'    => $code,
                'type'   => ucwords(str_replace('_', ' ', $type)),
                'expiry' => $expiry,
            ]
        );
    }

    public function sendFanIdApproved(User $user, string $fanId): void
    {
        $this->send(
            view: 'emails.fan_id_approved',
            subject: config('app.name') . ' — Your Fan ID is Ready: ' . $fanId,
            toEmail: $user->email,
            toName: $user->name,
            data: [
                'name'   => $user->name,
                'fan_id' => $fanId,
            ]
        );
    }

    public function sendBookingConfirmed(User $user, array $bookingData): void
    {
        $this->send(
            view: 'emails.booking_confirmed',
            subject: config('app.name') . ' — Booking Confirmed: ' . ($bookingData['match_name'] ?? ''),
            toEmail: $user->email,
            toName: $user->name,
            data: array_merge(['name' => $user->name], $bookingData)
        );
    }

    public function sendRefundProcessed(User $user, array $refundData): void
    {
        $this->send(
            view: 'emails.refund_processed',
            subject: config('app.name') . ' — Refund Processed for Booking #' . ($refundData['booking_id'] ?? ''),
            toEmail: $user->email,
            toName: $user->name,
            data: array_merge(['name' => $user->name], $refundData)
        );
    }
}
