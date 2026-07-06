<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $status === 'success' ? 'Stripe connected' : 'Stripe' }}</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
           background:#f8fafc; color:#0f172a; display:flex; align-items:center;
           justify-content:center; min-height:100vh; margin:0; }
    .card { text-align:center; padding:32px 28px; max-width:360px; }
    .icon { font-size:3rem; line-height:1; margin-bottom:12px; }
    .icon.success { color:#16a34a; }
    .icon.error   { color:#dc2626; }
    p { margin:8px 0; }
    .muted { color:#64748b; font-size:.9rem; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon {{ $status }}">{!! $status === 'success' ? '&#10003;' : '&#9888;' !!}</div>
    <p><strong>{{ $message }}</strong></p>
    <p class="muted">You can close this window.</p>
  </div>
  <script>
    (function () {
      // A script-opened popup can close itself even when COOP severed
      // the opener reference during the Stripe round-trip. Let the
      // message render for a beat, then close. If this isn't actually a
      // popup (window.close() no-ops in a normal tab), fall back to the
      // block editor so the user isn't stranded on a blank page. The
      // block form polls /stripe/status independently, so the connected
      // state reflects regardless.
      setTimeout(function () {
        window.close();
        setTimeout(function () {
          window.location.replace('/studio/edit#blocks');
        }, 400);
      }, 900);
    })();
  </script>
</body>
</html>
