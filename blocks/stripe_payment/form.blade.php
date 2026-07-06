<?php
  require_once base_path('blocks/stripe_payment/currencies.php');

  $defaultSuccessUrl = url('/@' . (auth()->user()->littlelink_name ?? ''));
  $defaultCancelUrl  = $defaultSuccessUrl;

  // LinkTypeViewController::getParamForm merges type_params JSON into
  // the view data, so these variables are set when editing an existing
  // block. For new blocks they're unset — we fall back to defaults.
  $currentMode = $mode ?? 'fixed_price';

  // Backward compat: pre-expansion blocks have amount_cents but no
  // options array. Synthesize a single-option array from the legacy
  // field so the form pre-fills correctly on edit.
  $optionsList = $options ?? [];
  if (empty($optionsList) && isset($amount_cents)) {
      $optionsList = [[
          'label'        => $link ?? 'Option 1',
          'amount_cents' => (int) $amount_cents,
      ]];
  }

  $currencyCode = strtolower($currency ?? 'usd');

  // Convert stored smallest-unit integers back to decimal strings for
  // pre-filling the form inputs.
  $fmt = function ($cents) use ($currencyCode) {
      if ($cents === null || $cents === '') return '';
      return stripe_payment_format_smallest_unit((int) $cents, $currencyCode);
  };

  $pinned    = stripe_payment_pinned_currencies();
  $allCurr   = stripe_payment_all_currencies();
  $remaining = array_diff_key($allCurr, $pinned);
?>

{{-- Stripe connection status + connect/disconnect, self-contained in
     the block (the Settings-page card was removed — this is now the
     single home for connecting Stripe). Payment blocks pay out to the
     page owner's connected account (users.stripe_account_id) set up via
     Stripe Connect OAuth — never by entering keys here.

     The connect flow runs in a POPUP window so the editor never
     navigates away: the block form can't host the OAuth page inline
     (Stripe refuses to be framed), so we window.open() a popup that
     self-closes when done. Rather than rely on cross-window messaging
     (COOP can sever the opener after the Stripe round-trip), the form
     polls /stripe/status and flips to "connected" when the account
     lands. Disconnect posts via fetch and swaps the banner in place. --}}
<div id="sp-connect-banner"
     data-connected="{{ !empty(auth()->user()->stripe_account_id ?? null) ? '1' : '0' }}"
     data-account="{{ auth()->user()->stripe_account_id ?? '' }}"
     class="mb-3"></div>

<script>
(function () {
    var banner = document.getElementById('sp-connect-banner');
    if (!banner) return;

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
                  '<div class="small">Stripe is connected — payments from this block pay out to your account' +
                    (account ? ' (<code>' + esc(account) + '</code>)' : '') + '.</div>' +
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
                  '<strong>Connect your Stripe account first</strong>' +
                  '<div class="small mb-2">This block routes payments to your own Stripe account — ' +
                    'it can\'t take payments until you connect one. A Stripe window opens when you click ' +
                    'below and closes itself when you\'re done.</div>' +
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
        // Popup opens as a top-level window (not inside the editor iframe),
        // so Stripe's no-framing policy doesn't apply.
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

    // Initial paint from the server-rendered state.
    if (banner.getAttribute('data-connected') === '1') {
        renderConnected(banner.getAttribute('data-account'));
    } else {
        renderNotConnected();
    }
})();
</script>

{{-- Select2 for the searchable currency picker. Loaded from CDN;
     admin-only so CDN latency is not a concern. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<select style="display:none" name="button" class="form-control"><option class="button button-default stripe_payment" value="default stripe_payment">Stripe payment</option></select>

<label for='title' class='form-label'>Section heading</label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Pay me" required />
<span class='small text-muted'>Text shown above the payment button</span><br>

<label for='link' class='form-label'>Button label</label>
<input type='text' name='link' value='{{ $link ?? '' }}' class='form-control' maxlength="50" placeholder="Pay now" required />
<span class='small text-muted'>Text on the main button. For multiple price options this appears above the option buttons; for a tip jar this is the button visitors click to open the amount input.</span><br>

{{-- Mode selector --}}
<label class='form-label' style='margin-top:20px;'>Payment mode</label>
<div class="form-check">
    <input class="form-check-input sp-mode-radio" type="radio" name="mode" id="sp-mode-fixed" value="fixed_price" @if($currentMode === 'fixed_price') checked @endif>
    <label class="form-check-label" for="sp-mode-fixed"><strong>Fixed price</strong> &mdash; one or more preset amounts</label>
</div>
<div class="form-check">
    <input class="form-check-input sp-mode-radio" type="radio" name="mode" id="sp-mode-tip" value="tip_jar" @if($currentMode === 'tip_jar') checked @endif>
    <label class="form-check-label" for="sp-mode-tip"><strong>Tip jar</strong> &mdash; visitor enters their own amount</label>
</div>

{{-- Fixed-price fields --}}
<div id="sp-fixed-fields" class="mt-3" @if($currentMode !== 'fixed_price') style="display:none" @endif>
    <p class="text-muted small">Configure up to three preset amounts. Visitors see one button per option. Only option 1 is required.</p>

    @for($i = 1; $i <= 3; $i++)
      @php
        $prevLabel  = $optionsList[$i - 1]['label']        ?? '';
        $prevAmount = $optionsList[$i - 1]['amount_cents'] ?? null;
      @endphp
      <div class="row mt-2">
        <div class="col-md-6">
          <label for="option_{{ $i }}_label" class="form-label">Option {{ $i }} label @if($i === 1)<span class="text-muted">(required)</span>@else<span class="text-muted">(optional)</span>@endif</label>
          <input type="text" name="option_{{ $i }}_label" id="option_{{ $i }}_label" value="{{ $prevLabel }}" class="form-control" maxlength="50" placeholder="{{ ['Basic','Standard','Premium'][$i-1] }}" @if($i === 1) @endif>
        </div>
        <div class="col-md-6">
          <label for="option_{{ $i }}_amount" class="form-label">Option {{ $i }} amount</label>
          <div class="input-group">
            <span class="input-group-text sp-currency-symbol">{{ stripe_payment_currency_symbol($currencyCode) }}</span>
            <input type="number" name="option_{{ $i }}_amount" id="option_{{ $i }}_amount" value="{{ $fmt($prevAmount) }}" class="form-control" min="0.01" step="0.01" placeholder="5.00">
          </div>
        </div>
      </div>
    @endfor
</div>

{{-- Tip-jar fields --}}
<div id="sp-tip-fields" class="mt-3" @if($currentMode !== 'tip_jar') style="display:none" @endif>
    <p class="text-muted small">Visitors click the button, enter their own amount, then proceed to Stripe. The button label above is what they click to open the amount input.</p>

    <div class="row mt-2">
        <div class="col-md-6">
            <label for="min_amount" class="form-label">Minimum amount <span class="text-muted">(optional)</span></label>
            <div class="input-group">
                <span class="input-group-text sp-currency-symbol">{{ stripe_payment_currency_symbol($currencyCode) }}</span>
                <input type="number" name="min_amount" id="min_amount" value="{{ $fmt($min_amount_cents ?? null) }}" class="form-control" min="0.01" step="0.01" placeholder="1.00">
            </div>
            <span class="small text-muted">Defaults to {{ stripe_payment_currency_symbol($currencyCode) }}1.00 if blank.</span>
        </div>
        <div class="col-md-6">
            <label for="suggested_amount" class="form-label">Suggested amount <span class="text-muted">(optional)</span></label>
            <div class="input-group">
                <span class="input-group-text sp-currency-symbol">{{ stripe_payment_currency_symbol($currencyCode) }}</span>
                <input type="number" name="suggested_amount" id="suggested_amount" value="{{ $fmt($suggested_amount_cents ?? null) }}" class="form-control" min="0.01" step="0.01" placeholder="5.00">
            </div>
            <span class="small text-muted">Pre-fills the visitor's input — remains editable.</span>
        </div>
    </div>
</div>

{{-- Currency picker (shared) --}}
<label for='currency' class='form-label' style='margin-top:20px;'>Currency</label>
<select name='currency' id='sp-currency' class='form-control' required>
    <optgroup label="Popular">
        @foreach($pinned as $code => $name)
            <option value="{{ $code }}" @if($code === $currencyCode) selected @endif>{{ strtoupper($code) }} &mdash; {{ $name }} ({{ stripe_payment_currency_symbol($code) }})</option>
        @endforeach
    </optgroup>
    <optgroup label="All currencies">
        @foreach($remaining as $code => $name)
            <option value="{{ $code }}" @if($code === $currencyCode) selected @endif>{{ strtoupper($code) }} &mdash; {{ $name }} ({{ stripe_payment_currency_symbol($code) }})</option>
        @endforeach
    </optgroup>
</select>
<span class='small text-muted'>Type to search by code or name. ISO 4217 three-letter code is stored (e.g. <code>usd</code>, <code>jpy</code>).</span><br>

<label for='product_description' class='form-label'>Product description</label>
<input type='text' name='product_description' value='{{ $product_description ?? '' }}' class='form-control' maxlength="200" required placeholder="One coffee for James" />
<span class='small text-muted'>Shown to the customer on Stripe's checkout page</span>

{{-- Success / cancel URLs are intentionally NOT shown in the form.
     The handler.php auto-fills both with the user's bio-page URL so
     visitors always end up back where they started. If a future user
     genuinely needs custom redirect URLs (e.g. a thank-you page),
     wrap these two inputs in a <details>Advanced settings</details>
     block — the storage shape and Stripe Checkout call already
     support arbitrary URLs. --}}

<script>
(function () {
    // Toggle between fixed-price and tip-jar field groups based on
    // the mode radio selection.
    var fixed = document.getElementById('sp-fixed-fields');
    var tip   = document.getElementById('sp-tip-fields');
    document.querySelectorAll('.sp-mode-radio').forEach(function (r) {
        r.addEventListener('change', function () {
            if (r.value === 'fixed_price' && r.checked) {
                fixed.style.display = '';
                tip.style.display   = 'none';
            } else if (r.value === 'tip_jar' && r.checked) {
                fixed.style.display = 'none';
                tip.style.display   = '';
            }
        });
    });

    // Live-update the currency-symbol prefix spans when the dropdown changes.
    var symbols = @json(array_combine(
        array_keys(stripe_payment_all_currencies()),
        array_map('stripe_payment_currency_symbol', array_keys(stripe_payment_all_currencies()))
    ));
    var $curr = document.getElementById('sp-currency');
    function syncSymbols() {
        var sym = symbols[$curr.value] || ($curr.value || '').toUpperCase();
        document.querySelectorAll('.sp-currency-symbol').forEach(function (el) { el.textContent = sym; });
    }
    $curr.addEventListener('change', syncSymbols);

    // Initialize Select2 for searchable currency picker. Poll up to 2s
    // because the Select2 <script> above is inserted alongside this
    // form via AJAX and may not have finished loading yet.
    (function tryInitSelect2(attempts) {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery('#sp-currency').select2({
                width: '100%',
                templateSelection: function (data) { return data.text; },
            }).on('change', syncSymbols);
        } else if (attempts < 20) {
            setTimeout(function () { tryInitSelect2(attempts + 1); }, 100);
        }
    })(0);
})();
</script>

@include('studio.partials.block-collapsed-toggle')
