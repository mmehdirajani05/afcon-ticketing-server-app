<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #f0f0f0; color: #333; }
        .wrap { max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .hdr { background: #2196a6; padding: 32px 24px; text-align: center; }
        .hdr h1 { color: #ffffff; font-size: 28px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
        .body { padding: 36px 32px; }
        .body p { font-size: 15px; color: #444444; line-height: 1.7; margin-bottom: 10px; }
        .otp-wrap { text-align: center; margin: 28px 0 20px; }
        .otp-code { display: inline-block; font-size: 44px; font-weight: 900; letter-spacing: 10px; color: #27ae60; font-family: 'Courier New', monospace; border-left: 4px solid #27ae60; padding-left: 16px; }
        .validity { text-align: center; font-size: 13px; color: #888888; margin-bottom: 28px; }
        .divider { border: none; border-top: 1px solid #eeeeee; margin: 24px 0; }
        .warn { font-size: 13px; color: #999999; line-height: 1.6; }
        .ftr { background: #f7f7f7; padding: 18px 24px; text-align: center; font-size: 12px; color: #bbbbbb; border-top: 1px solid #eeeeee; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="hdr">
        <h1>{{ $app }}</h1>
    </div>

    <div class="body">
        <p>Hello,</p>
        <p>We received a verification request for your account. Please use the code below to complete the process:</p>

        <div class="otp-wrap">
            <span class="otp-code">{{ $otp }}</span>
        </div>

        <p class="validity">This code is valid for {{ $expiry }} minutes</p>

        <hr class="divider">

        <p class="warn">
            If you did not request this, please ignore this email.
            Do not share this code with anyone — our team will never ask for it.
        </p>
    </div>

    <div class="ftr">
        &copy; {{ $app }} &nbsp;&middot;&nbsp; This is an automated message, please do not reply.
    </div>

</div>
</body>
</html>
