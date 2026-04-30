<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends a pre-rendered HTML email in the background via the queue.
 *
 * By dispatching this job instead of calling Mail::html() inline, the HTTP
 * response is returned to the client immediately — SMTP handshake and
 * Gmail delivery happen asynchronously, with no impact on response time.
 *
 * Queue: QUEUE_CONNECTION=database (configured in .env)
 * Worker: php artisan queue:work  (run this on the server)
 */
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry up to 3 times on SMTP failure. */
    public int $tries = 3;

    /** Wait 60 seconds between retries (avoids hitting Gmail rate limits). */
    public int $backoff = 60;

    /** Discard the job if it hasn't started within 10 minutes (OTP would be expired anyway). */
    public int $timeout = 30;

    public function __construct(
        public readonly string $toEmail,
        public readonly string $toName,
        public readonly string $subject,
        public readonly string $body,
    ) {}

    public function handle(): void
    {
        Mail::html($this->body, function ($message) {
            $message->to($this->toEmail, $this->toName)->subject($this->subject);
        });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendEmailJob permanently failed', [
            'to'      => $this->toEmail,
            'subject' => $this->subject,
            'error'   => $exception->getMessage(),
        ]);
    }
}
