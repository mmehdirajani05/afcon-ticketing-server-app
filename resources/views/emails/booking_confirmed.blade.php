@extends('emails.layout')

@section('content')

<p style="margin:0 0 20px;font-size:15px;color:#333333;line-height:1.6;">
  Hello <strong>{{ $name }}</strong>, your ticket has been confirmed!
</p>

{{-- Booking details table --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="border-collapse:collapse;font-size:14px;">
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;width:40%;">Match</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;">{{ $match_name }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;">Date</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;">{{ $match_date }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;">Venue</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;">{{ $venue }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;">Category</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;">{{ $ticket_category }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               font-weight:700;color:#555555;">Booking Ref</td>
    <td style="padding:10px 12px;border-bottom:1px solid #eeeeee;
               color:#333333;font-family:'Courier New',Courier,monospace;
               font-weight:700;">{{ $caf_ticket_ref }}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;font-weight:700;color:#555555;">Amount Paid</td>
    <td style="padding:10px 12px;color:#27ae60;font-weight:700;">
      {{ $currency }} {{ $amount }}
    </td>
  </tr>
</table>

<p style="margin:24px 0 0;font-size:13px;color:#aaaaaa;line-height:1.7;">
  Download your digital ticket from the app. Keep this email for your records.
</p>

@endsection
