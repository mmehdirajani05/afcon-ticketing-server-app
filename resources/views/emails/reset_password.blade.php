@extends('emails.layout')

@section('content')

<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">
  Hello <strong>{{ $name }}</strong>,
</p>
<p style="margin:0 0 28px;font-size:15px;color:#444444;line-height:1.7;">
  We received a request to reset your password.
  Use the code below &mdash; it expires in <strong>{{ $expiry }} minutes</strong>.
</p>

{{-- OTP highlight box (same style, different color to indicate password action) --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td align="center" style="padding:4px 0 8px;">
      <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="border-left:5px solid #e67e22;
                     padding:8px 20px;">
            <span style="font-size:48px;font-weight:900;color:#e67e22;
                         letter-spacing:8px;font-family:'Courier New',Courier,monospace;">
              {{ $otp }}
            </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:8px 0 28px;">
      <span style="font-size:13px;color:#999999;">
        Password reset code &mdash; valid for {{ $expiry }} minutes
      </span>
    </td>
  </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr><td style="border-top:1px solid #eeeeee;padding-bottom:20px;"></td></tr>
</table>

<p style="margin:0;font-size:13px;color:#aaaaaa;line-height:1.7;">
  If you did not request a password reset, please ignore this email.
  Your password will not change.
</p>

@endsection
