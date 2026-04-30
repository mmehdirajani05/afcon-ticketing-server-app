@extends('emails.layout')

@section('content')

<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">
  Hello <strong>{{ $name }}</strong>,
</p>
<p style="margin:0 0 20px;font-size:15px;color:#444444;line-height:1.7;">
  Your refund of <strong>{{ $currency }} {{ $amount }}</strong>
  for booking <strong>#{{ $booking_id }}</strong> has been processed.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="border-collapse:collapse;font-size:14px;">
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;width:40%;">Refund Reference</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;font-family:'Courier New',Courier,monospace;
               font-weight:700;">{{ $refund_transaction_id }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;font-weight:700;color:#555555;">Amount</td>
    <td style="padding:10px 12px;color:#e67e22;font-weight:700;">
      {{ $currency }} {{ $amount }}
    </td>
  </tr>
</table>

<p style="margin:20px 0 0;font-size:13px;color:#aaaaaa;line-height:1.7;">
  Please allow 3–5 business days for the amount to reflect in your account.
</p>

@endsection
