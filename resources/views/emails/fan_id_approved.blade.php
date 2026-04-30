@extends('emails.layout')

@section('content')

<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">
  Hello <strong>{{ $name }}</strong>,
</p>
<p style="margin:0 0 28px;font-size:15px;color:#444444;line-height:1.7;">
  Great news! Your account has been approved and your Fan ID has been successfully assigned.
  You can now purchase your desired tickets.
</p>

{{-- Fan ID highlight box --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td align="center" style="padding:4px 0 8px;">
      <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="border-left:5px solid #27ae60;
                     padding:10px 20px;">
            <span style="font-size:28px;font-weight:900;color:#27ae60;
                         letter-spacing:4px;font-family:'Courier New',Courier,monospace;">
              {{ $fan_id }}
            </span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="center" style="padding:8px 0 28px;">
      <span style="font-size:13px;color:#999999;">
        Your Fan ID &mdash; keep this safe for your records
      </span>
    </td>
  </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr><td style="border-top:1px solid #eeeeee;padding-bottom:20px;"></td></tr>
</table>

<p style="margin:0;font-size:13px;color:#aaaaaa;line-height:1.7;">
  If you have any questions, please contact our support team. Enjoy the games!
</p>

@endsection
