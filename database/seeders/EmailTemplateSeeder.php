<?php

namespace Database\Seeders;

use App\Constants\AppConstant;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'    => AppConstant::EMAIL_TEMPLATE_OTP,
                'subject' => '{{app}} — Your Verification Code: {{otp}}',
                'body'    => $this->otpTemplate(),
            ],
            [
                'name'    => AppConstant::EMAIL_TEMPLATE_RESET_PW,
                'subject' => '{{app}} — Password Reset Request',
                'body'    => $this->resetPasswordTemplate(),
            ],
            [
                'name'    => AppConstant::EMAIL_TEMPLATE_BOOKING,
                'subject' => '{{app}} — Booking Confirmed: {{match_name}}',
                'body'    => $this->bookingConfirmedTemplate(),
            ],
            [
                'name'    => AppConstant::EMAIL_TEMPLATE_REFUND,
                'subject' => '{{app}} — Refund Processed for Booking #{{booking_id}}',
                'body'    => $this->refundTemplate(),
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                array_merge($template, ['is_active' => true])
            );
        }
    }

    private function otpTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;background:#f0f0f0;color:#333}
.wrap{max-width:480px;margin:40px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)}
.hdr{background:#2196a6;padding:32px 24px;text-align:center}
.hdr h1{color:#fff;font-size:28px;font-weight:900;letter-spacing:2px;text-transform:uppercase}
.body{padding:36px 32px}
.body p{font-size:15px;color:#444;line-height:1.7;margin-bottom:10px}
.otp-wrap{text-align:center;margin:28px 0 20px}
.otp-code{display:inline-block;font-size:44px;font-weight:900;letter-spacing:10px;color:#27ae60;font-family:'Courier New',monospace;border-left:4px solid #27ae60;padding-left:16px}
.validity{text-align:center;font-size:13px;color:#888;margin-bottom:28px}
.divider{border:none;border-top:1px solid #eee;margin:24px 0}
.warn{font-size:13px;color:#999;line-height:1.6}
.ftr{background:#f7f7f7;padding:18px 24px;text-align:center;font-size:12px;color:#bbb;border-top:1px solid #eee}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr"><h1>{{app}}</h1></div>
  <div class="body">
    <p>Hello,</p>
    <p>We received a verification request for your account. Please use the code below to complete the process:</p>
    <div class="otp-wrap">
      <span class="otp-code">{{otp}}</span>
    </div>
    <p class="validity">This code is valid for {{expiry}} minutes</p>
    <hr class="divider">
    <p class="warn">If you did not request this, please ignore this email. Do not share this code with anyone — our team will never ask for it.</p>
  </div>
  <div class="ftr">© {{app}} &nbsp;·&nbsp; This is an automated message, please do not reply.</div>
</div>
</body>
</html>
HTML;
    }

    private function resetPasswordTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>body{font-family:Arial,sans-serif;background:#f4f4f4;color:#333}.wrap{max-width:560px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden}.hdr{background:#003366;padding:30px;color:#fff;text-align:center}.body{padding:32px}.otp-box{background:#fff3cd;border:2px dashed #e0a800;border-radius:8px;padding:24px;text-align:center;margin:20px 0}.otp-code{font-size:40px;font-weight:900;letter-spacing:10px;color:#856404;font-family:monospace}.ftr{background:#f9f9f9;padding:18px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee}</style>
</head><body><div class="wrap">
<div class="hdr"><h2>🔑 Password Reset</h2><p>{{app}}</p></div>
<div class="body">
<p>Hello <strong>{{name}}</strong>,</p>
<p>Use this code to reset your password. It expires in <strong>{{expiry}} minutes</strong>.</p>
<div class="otp-box"><div class="otp-code">{{otp}}</div></div>
<p>If you did not request a password reset, please ignore this email. Your password will not change.</p>
</div>
<div class="ftr">© {{app}}</div>
</div></body></html>
HTML;
    }

    private function bookingConfirmedTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>body{font-family:Arial,sans-serif;background:#f4f4f4;color:#333}.wrap{max-width:560px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden}.hdr{background:linear-gradient(135deg,#003366,#009900);padding:30px;color:#fff;text-align:center}.body{padding:32px}.info-table{width:100%;border-collapse:collapse}.info-table td{padding:10px 12px;border-bottom:1px solid #eee;font-size:14px}.info-table td:first-child{font-weight:700;color:#555;width:40%}.ftr{background:#f9f9f9;padding:18px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee}</style>
</head><body><div class="wrap">
<div class="hdr"><h2>✅ Booking Confirmed!</h2><p>{{app}}</p></div>
<div class="body">
<p>Hello <strong>{{name}}</strong>, your ticket has been confirmed.</p>
<table class="info-table">
<tr><td>Match</td><td>{{match_name}}</td></tr>
<tr><td>Date</td><td>{{match_date}}</td></tr>
<tr><td>Venue</td><td>{{venue}}</td></tr>
<tr><td>Category</td><td>{{ticket_category}}</td></tr>
<tr><td>Booking Ref</td><td>{{caf_ticket_ref}}</td></tr>
<tr><td>Amount Paid</td><td>{{currency}} {{amount}}</td></tr>
</table>
<p style="margin-top:20px">Download your digital ticket from the app.</p>
</div>
<div class="ftr">© {{app}} | Keep this email for your records.</div>
</div></body></html>
HTML;
    }

    private function refundTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>body{font-family:Arial,sans-serif;background:#f4f4f4;color:#333}.wrap{max-width:560px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden}.hdr{background:#6c757d;padding:30px;color:#fff;text-align:center}.body{padding:32px}.ftr{background:#f9f9f9;padding:18px;text-align:center;font-size:12px;color:#aaa;border-top:1px solid #eee}</style>
</head><body><div class="wrap">
<div class="hdr"><h2>💳 Refund Processed</h2><p>{{app}}</p></div>
<div class="body">
<p>Hello <strong>{{name}}</strong>,</p>
<p>Your refund of <strong>{{currency}} {{amount}}</strong> for booking <strong>#{{booking_id}}</strong> has been processed.</p>
<p style="margin-top:12px">Refund Reference: <strong>{{refund_transaction_id}}</strong></p>
<p style="margin-top:12px;color:#555;font-size:13px">Please allow 3–5 business days for the amount to reflect in your account.</p>
</div>
<div class="ftr">© {{app}}</div>
</div></body></html>
HTML;
    }
}
