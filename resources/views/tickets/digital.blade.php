<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AFCON 2027 — Digital Ticket</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f4f4; color: #222; }

        .ticket { width: 650px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 20px rgba(0,0,0,0.15); }

        /* Header band */
        .ticket-header { background: linear-gradient(135deg, #003366 0%, #009900 100%); padding: 28px 32px; color: white; display: flex; justify-content: space-between; align-items: center; }
        .ticket-header .logo { font-size: 22px; font-weight: 900; letter-spacing: 1px; }
        .ticket-header .edition { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .ticket-header .category-badge { background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); border-radius: 8px; padding: 8px 16px; text-align: center; }
        .ticket-header .category-badge .cat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.85; }
        .ticket-header .category-badge .cat-value { font-size: 18px; font-weight: 800; }

        /* Divider with perforated edge effect */
        .perforated { border-top: 3px dashed #e0e0e0; margin: 0; position: relative; }
        .perforated::before, .perforated::after {
            content: ''; width: 24px; height: 24px; background: #f4f4f4;
            border-radius: 50%; position: absolute; top: -13px;
        }
        .perforated::before { left: -12px; }
        .perforated::after  { right: -12px; }

        /* Match details */
        .match-section { padding: 24px 32px; }
        .match-title { font-size: 22px; font-weight: 800; color: #003366; text-align: center; margin-bottom: 6px; }
        .match-subtitle { text-align: center; font-size: 13px; color: #777; margin-bottom: 20px; }

        .match-info-grid { display: table; width: 100%; border-collapse: collapse; }
        .match-info-row  { display: table-row; }
        .match-info-cell { display: table-cell; padding: 8px 12px; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .match-info-cell.label { font-weight: 700; color: #555; width: 40%; }
        .match-info-cell.value { color: #222; }

        /* Bottom strip */
        .bottom-strip { display: table; width: 100%; background: #f8f8f8; border-top: 1px solid #eee; }
        .strip-left  { display: table-cell; padding: 24px 32px; vertical-align: middle; width: 65%; }
        .strip-right { display: table-cell; padding: 24px 32px; text-align: center; vertical-align: middle; border-left: 2px dashed #ddd; }

        .fan-id-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #999; }
        .fan-id-value { font-size: 15px; font-weight: 800; font-family: 'Courier New', monospace; color: #003366; margin-top: 4px; word-break: break-all; }

        .booking-ref-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #999; margin-top: 14px; }
        .booking-ref-value  { font-size: 13px; font-weight: 700; color: #555; margin-top: 3px; }

        .qr-image { width: 130px; height: 130px; border: 3px solid #003366; border-radius: 8px; padding: 4px; }
        .qr-label { font-size: 10px; color: #999; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }

        /* Footer */
        .ticket-footer { background: #003366; color: rgba(255,255,255,0.7); text-align: center; padding: 14px; font-size: 11px; }
        .ticket-footer strong { color: #fff; }

        .validity-note { text-align: center; font-size: 11px; color: #e53e3e; margin: 10px 32px; padding: 10px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; }
    </style>
</head>
<body>
<div class="ticket">

    {{-- Header --}}
    <div class="ticket-header">
        <div>
            <div class="logo">🏆 AFCON 2027</div>
            <div class="edition">Africa Cup of Nations · Official Ticket</div>
        </div>
        <div class="category-badge">
            <div class="cat-label">Category</div>
            <div class="cat-value">{{ strtoupper($booking->ticket_category ?? 'GENERAL') }}</div>
        </div>
    </div>

    {{-- Match Details --}}
    <div class="match-section">
        <div class="match-title">{{ $booking->match_name ?? 'Match TBD' }}</div>
        <div class="match-subtitle">Group Stage / Knockout Round</div>

        <div class="match-info-grid">
            <div class="match-info-row">
                <div class="match-info-cell label">Date &amp; Time</div>
                <div class="match-info-cell value">
                    {{ $booking->match_date ? \Carbon\Carbon::parse($booking->match_date)->format('D, d M Y · H:i') . ' UTC' : 'TBD' }}
                </div>
            </div>
            <div class="match-info-row">
                <div class="match-info-cell label">Venue</div>
                <div class="match-info-cell value">{{ $booking->venue ?? 'TBD' }}</div>
            </div>
            @if($booking->seat_info)
            <div class="match-info-row">
                <div class="match-info-cell label">Seat</div>
                <div class="match-info-cell value">{{ $booking->seat_info }}</div>
            </div>
            @endif
            <div class="match-info-row">
                <div class="match-info-cell label">Ticket Holder</div>
                <div class="match-info-cell value">{{ $user->name }}</div>
            </div>
            <div class="match-info-row">
                <div class="match-info-cell label">Amount Paid</div>
                <div class="match-info-cell value">TZS {{ number_format($booking->amount, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="perforated"></div>

    {{-- Bottom strip: Fan ID + QR --}}
    <div class="bottom-strip">
        <div class="strip-left">
            <div class="fan-id-label">Fan ID</div>
            <div class="fan-id-value">{{ $booking->fan_id ?? 'N/A' }}</div>

            <div class="booking-ref-label">Booking Reference</div>
            <div class="booking-ref-value">{{ $booking->caf_ticket_ref ?? '#' . $booking->id }}</div>
        </div>
        <div class="strip-right">
            @if($qrCodeDataUri && !str_ends_with($qrCodeDataUri, 'QR_ERROR'))
                <img class="qr-image" src="{{ $qrCodeDataUri }}" alt="QR Code">
            @else
                <div style="width:130px;height:130px;border:3px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:11px;color:#999;">QR unavailable</div>
            @endif
            <div class="qr-label">Scan at gate</div>
        </div>
    </div>

    <div class="validity-note">
        ⚠️ This ticket is valid for ONE entry only. Non-transferable. Valid ID required at entry.
    </div>

    {{-- Footer --}}
    <div class="ticket-footer">
        Generated on <strong>{{ $generatedAt }}</strong> &nbsp;·&nbsp;
        <strong>AFCON 2027</strong> &nbsp;·&nbsp; afcon2027.com
    </div>

</div>
</body>
</html>
