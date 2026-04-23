<?php

namespace App\Services\Email;

use App\Constants\AppConstant;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    /**
     * Send an email using a named template from DB with variable substitution.
     * Falls back to a plain text email if template is not found.
     */
    public function send(string $templateName, string $toEmail, string $toName, array $variables = []): void
    {
        $template = EmailTemplate::where('name', $templateName)->where('is_active', true)->first();

        if ($template) {
            $subject = $template->renderSubject($variables);
            $body    = $template->render($variables);
        } else {
            // Fallback so the system never silently fails on missing template
            $subject = $this->fallbackSubject($templateName, $variables);
            $body    = $this->fallbackBody($templateName, $variables);
        }

        try {
            Mail::html($body, function ($message) use ($toEmail, $toName, $subject) {
                $message->to($toEmail, $toName)->subject($subject);
            });
        } catch (\Throwable $e) {
            // Log but do not throw — email failure should not block critical flows
            Log::error('EmailTemplateService: failed to send email', [
                'template' => $templateName,
                'to'       => $toEmail,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function sendOtp(User $user, string $code, string $type): void
    {
        $this->send(AppConstant::EMAIL_TEMPLATE_OTP, $user->email, $user->name, [
            'name'    => $user->name,
            'otp'     => $code,
            'type'    => ucwords(str_replace('_', ' ', $type)),
            'expiry'  => config('otp.expiry_minutes', AppConstant::OTP_EXPIRY_MINUTES),
            'app'     => config('app.name'),
        ]);
    }

    public function sendBookingConfirmed(User $user, array $bookingData): void
    {
        $this->send(AppConstant::EMAIL_TEMPLATE_BOOKING, $user->email, $user->name, array_merge(
            ['name' => $user->name, 'app' => config('app.name')],
            $bookingData
        ));
    }

    public function sendRefundProcessed(User $user, array $refundData): void
    {
        $this->send(AppConstant::EMAIL_TEMPLATE_REFUND, $user->email, $user->name, array_merge(
            ['name' => $user->name, 'app' => config('app.name')],
            $refundData
        ));
    }

    private function fallbackSubject(string $templateName, array $variables): string
    {
        return match ($templateName) {
            AppConstant::EMAIL_TEMPLATE_OTP     => config('app.name') . ' — Your OTP Code',
            AppConstant::EMAIL_TEMPLATE_BOOKING => config('app.name') . ' — Booking Confirmed',
            AppConstant::EMAIL_TEMPLATE_REFUND  => config('app.name') . ' — Refund Processed',
            default                             => config('app.name') . ' — Notification',
        };
    }

    private function fallbackBody(string $templateName, array $variables): string
    {
        if ($templateName === AppConstant::EMAIL_TEMPLATE_OTP) {
            $otp    = $variables['otp'] ?? '';
            $expiry = $variables['expiry'] ?? 10;
            $name   = $variables['name'] ?? 'User';

            return <<<HTML
            <p>Hi {$name},</p>
            <p>Your one-time verification code is:</p>
            <h2 style="letter-spacing:6px;">{$otp}</h2>
            <p>This code expires in <strong>{$expiry} minutes</strong>. Do not share it with anyone.</p>
            HTML;
        }

        return '<p>' . implode('<br>', array_map(
            fn ($k, $v) => "<strong>{$k}:</strong> {$v}",
            array_keys($variables),
            $variables
        )) . '</p>';
    }
}
