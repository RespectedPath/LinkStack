{{--
    Stripe connect/disconnect panel — the single connection UI, shared by
    the Stripe payment block editor AND the Settings → Integrations card.
    The connection is account-level (one users.stripe_account_id serves
    every payment block), so managing it in either place affects all
    blocks the same way.

    Connect runs in a popup (Stripe refuses to be framed, and this keeps
    the host page from navigating away); the panel polls /stripe/status
    and flips to "connected" in place when the account lands — polling
    rather than postMessage because COOP can sever the popup↔opener link
    after the cross-origin Stripe round-trip. Disconnect posts via fetch
    and swaps back in place.

    Only one instance renders per page (the block editor shows one block
    at a time; Settings shows one card), so the fixed ids are safe.
--}}
<div id="sp-connect-banner"
     data-connected="{{ !empty(auth()->user()->stripe_account_id ?? null) ? '1' : '0' }}"
     data-account="{{ auth()->user()->stripe_account_id ?? '' }}"
     class="mb-0"></div>

<script>
(function () {
    var banner = document.getElementById('sp-connect-banner');
    if (!banner || banner.dataset.mmInit === '1') return;
    banner.dataset.mmInit = '1';

    var CSRF           = @json(csrf_token());
    var CONNECT_URL    = @json(route('stripe.connect'));
    var STATUS_URL     = @json(route('stripe.status'));
    var DISCONNECT_URL = @json(route('stripe.disconnect'));

    var popupHandle = null;
    var pollTimer   = null;

    function esc(s) {
        return String(s || '').replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function renderConnected(account) {
        banner.innerHTML =
            '<div class="alert alert-success py-2 mb-0">' +
              '<div class="d-flex align-items-start gap-2">' +
                '<i class="bi bi-check-circle-fill mt-1"></i>' +
                '<div class="flex-grow-1">' +
                  '<div class="small">Stripe is connected — payments pay out to your account' +
                    (account ? ' (<code>' + esc(account) + '</code>)' : '') + '. This covers every payment block on your page.</div>' +
                  '<button type="button" id="sp-disconnect" class="btn btn-sm btn-outline-danger mt-2">' +
                    '<i class="bi bi-x-circle"></i> Disconnect</button>' +
                '</div>' +
              '</div>' +
            '</div>';
        var db = document.getElementById('sp-disconnect');
        if (db) db.addEventListener('click', doDisconnect);
    }

    function renderNotConnected() {
        banner.innerHTML =
            '<div class="alert alert-warning mb-0">' +
              '<div class="d-flex align-items-start gap-2">' +
                '<i class="bi bi-exclamation-triangle-fill mt-1"></i>' +
                '<div>' +
                  '<strong>Connect your Stripe account</strong>' +
                  '<div class="small mb-2">Payment blocks route payouts to your own Stripe account — ' +
                    'they can\'t take payments until one is connected. A Stripe window opens when you ' +
                    'click below and closes itself when you\'re done. Zero platform fee; Stripe\'s ' +
                    'standard processing fee still applies.</div>' +
                  '<button type="button" id="sp-connect-btn" class="btn btn-sm btn-primary">' +
                    '<i class="bi bi-box-arrow-up-right"></i> Connect Stripe</button>' +
                  '<span id="sp-connect-waiting" class="small text-muted ms-2" style="display:none;">' +
                    'Waiting for Stripe…</span>' +
                '</div>' +
              '</div>' +
            '</div>';
        var cb = document.getElementById('sp-connect-btn');
        if (cb) cb.addEventListener('click', startConnect);
    }

    function startConnect() {
        var waiting = document.getElementById('sp-connect-waiting');
        if (waiting) waiting.style.display = '';
        var w = 600, h = 850;
        var x = Math.max(0, (screen.width  - w) / 2);
        var y = Math.max(0, (screen.height - h) / 2);
        popupHandle = window.open(CONNECT_URL, 'stripeConnect',
            'popup,width=' + w + ',height=' + h + ',left=' + x + ',top=' + y);
        startPolling();
    }

    function startPolling() {
        var elapsed = 0;
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(function () {
            elapsed += 2000;
            fetch(STATUS_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d && d.connected) {
                        stopPolling();
                        try { if (popupHandle && !popupHandle.closed) popupHandle.close(); } catch (e) {}
                        renderConnected(d.account_id);
                    }
                })
                .catch(function () {});
            if (elapsed >= 180000) stopPolling(); // give up after ~3 min
        }, 2000);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        var waiting = document.getElementById('sp-connect-waiting');
        if (waiting) waiting.style.display = 'none';
    }

    function doDisconnect() {
        if (!confirm('Disconnect this Stripe account? Payment blocks will stop working until you reconnect.')) return;
        fetch(DISCONNECT_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).then(function () { renderNotConnected(); })
          .catch(function () { renderNotConnected(); });
    }

    if (banner.getAttribute('data-connected') === '1') {
        renderConnected(banner.getAttribute('data-account'));
    } else {
        renderNotConnected();
    }
})();
</script>
