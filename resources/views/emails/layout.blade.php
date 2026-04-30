<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{{ $app ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f2f2f2;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0"
       style="background-color:#f2f2f2;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="480" cellpadding="0" cellspacing="0" border="0"
             style="background:#ffffff;border-radius:8px;overflow:hidden;
                    box-shadow:0 2px 16px rgba(0,0,0,0.09);max-width:480px;">

        {{-- ── Header ── --}}
        <tr>
          <td style="background-color:#008EC0;padding:32px 24px;text-align:center;">
            <h1 style="margin:0;color:#ffffff;font-size:32px;font-weight:900;
                       letter-spacing:3px;text-transform:uppercase;">
              {{ $app ?? config('app.name') }}
            </h1>
          </td>
        </tr>

        {{-- ── Content injected by each email type ── --}}
        <tr>
          <td style="padding:36px 36px 28px;">
            @yield('content')
          </td>
        </tr>

        {{-- ── Footer ── --}}
        <tr>
          <td style="background-color:#f8f8f8;padding:16px 24px;
                     text-align:center;border-top:1px solid #eeeeee;">
            <span style="font-size:12px;color:#cccccc;">
              &copy; {{ $app ?? config('app.name') }}
              &nbsp;&middot;&nbsp;
              This is an automated message, please do not reply.
            </span>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
