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

@include('studio.partials.stripe-connect-panel')

{{-- Select2 for the searchable currency picker. Loaded from CDN;
     admin-only so CDN latency is not a concern. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script nonce="{{ csp_nonce() }}" src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<select style="display:none" name="button" class="form-control"><option class="button button-default stripe_payment" value="default stripe_payment">Stripe payment</option></select>

<label for='title' class='form-label'>Section heading</label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Pay me" required />
<span class='small text-muted'>Text shown above the payment button</span><br>

<label for='link' class='form-label'>Button label</label>
<input type='text' name='link' value='{{ $link ?? '' }}' class='form-control' maxlength="50" placeholder="Pay now" required />
<span class='small text-muted'>The call to action shown to visitors. For a single price or a tip jar it's the text on the button; with multiple preset amounts it appears as a prompt just above the option buttons.</span><br>

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
    <p class="text-muted small">For a single price, just set <strong>Option 1 amount</strong> &mdash; the button uses the <strong>Button label</strong> above. To offer choices, add 2&ndash;3 amounts (visitors see one button each); labels are optional and only used to name those choices.</p>

    @for($i = 1; $i <= 3; $i++)
      @php
        $prevLabel  = $optionsList[$i - 1]['label']        ?? '';
        $prevAmount = $optionsList[$i - 1]['amount_cents'] ?? null;
      @endphp
      <div class="row mt-2">
        <div class="col-md-6">
          <label for="option_{{ $i }}_label" class="form-label">Option {{ $i }} label <span class="text-muted">(optional)</span></label>
          <input type="text" name="option_{{ $i }}_label" id="option_{{ $i }}_label" value="{{ $prevLabel }}" class="form-control" maxlength="50" placeholder="{{ ['Basic','Standard','Premium'][$i-1] }}">
        </div>
        <div class="col-md-6">
          <label for="option_{{ $i }}_amount" class="form-label">Option {{ $i }} amount @if($i === 1)<span class="text-muted">(required)</span>@endif</label>
          <div class="input-group">
            <span class="input-group-text sp-currency-symbol">{{ stripe_payment_currency_symbol($currencyCode) }}</span>
            <input type="number" name="option_{{ $i }}_amount" id="option_{{ $i }}_amount" value="{{ $fmt($prevAmount) }}" class="form-control" min="0.01" step="0.01" placeholder="e.g. 5.00">
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
                <input type="number" name="min_amount" id="min_amount" value="{{ $fmt($min_amount_cents ?? null) }}" class="form-control" min="0.01" step="0.01" placeholder="e.g. 1.00">
            </div>
            <span class="small text-muted">Defaults to {{ stripe_payment_currency_symbol($currencyCode) }}1.00 if blank.</span>
        </div>
        <div class="col-md-6">
            <label for="suggested_amount" class="form-label">Suggested amount <span class="text-muted">(optional)</span></label>
            <div class="input-group">
                <span class="input-group-text sp-currency-symbol">{{ stripe_payment_currency_symbol($currencyCode) }}</span>
                <input type="number" name="suggested_amount" id="suggested_amount" value="{{ $fmt($suggested_amount_cents ?? null) }}" class="form-control" min="0.01" step="0.01" placeholder="e.g. 5.00">
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

<script nonce="{{ csp_nonce() }}">
(function () {
    // Toggle between fixed-price and tip-jar field groups based on
    // the mode radio selection.
    var fixed   = document.getElementById('sp-fixed-fields');
    var tip     = document.getElementById('sp-tip-fields');
    var opt1amt = document.getElementById('option_1_amount');

    // Show the fields for the chosen mode and mark Option 1's amount
    // required only while fixed-price is active. Doing this client-side
    // means an empty amount is caught by the browser before submit — so
    // the user never round-trips to the server and loses their entries.
    // (option_1_amount must NOT stay required while hidden, or the
    // tip-jar form can't submit.)
    function applyMode(mode) {
        var isFixed = (mode !== 'tip_jar');
        fixed.style.display = isFixed ? '' : 'none';
        tip.style.display   = isFixed ? 'none' : '';
        if (opt1amt) { opt1amt.required = isFixed; }
    }
    document.querySelectorAll('.sp-mode-radio').forEach(function (r) {
        r.addEventListener('change', function () {
            if (r.checked) { applyMode(r.value); }
        });
    });
    var checkedMode = document.querySelector('.sp-mode-radio:checked');
    applyMode(checkedMode ? checkedMode.value : 'fixed_price');

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
    // because the Select2 script above is inserted alongside this
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
