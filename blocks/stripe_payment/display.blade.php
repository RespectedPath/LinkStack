<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

@php
    require_once base_path('blocks/stripe_payment/currencies.php');

    // Decode the (public-safe) config. Amount / currency / description
    // / URLs are all public. The Stripe secret is in .env, the
    // connected account id is on users.stripe_account_id; neither is
    // rendered here.
    $sp = json_decode($link->type_params ?? '{}', true);
    if (!is_array($sp)) { $sp = []; }

    $mode         = (string) ($sp['mode'] ?? 'fixed_price');
    $currencyCode = strtolower((string) ($sp['currency'] ?? 'usd'));
    $symbol       = stripe_payment_currency_symbol($currencyCode);
    $currencyUp   = strtoupper($currencyCode);

    // Backward compat: if options absent but legacy amount_cents is
    // set, synthesize a single-option list so pre-expansion blocks
    // render unchanged.
    $options = $sp['options'] ?? [];
    if (empty($options) && isset($sp['amount_cents'])) {
        $options = [[
            'label'        => $link->link ?: 'Pay',
            'amount_cents' => (int) $sp['amount_cents'],
        ]];
    }

    $minSmallest       = isset($sp['min_amount_cents']) ? (int) $sp['min_amount_cents'] : 100;
    $suggestedSmallest = $sp['suggested_amount_cents'] ?? null;
    $suggestedDecimal  = $suggestedSmallest !== null
        ? stripe_payment_format_smallest_unit((int) $suggestedSmallest, $currencyCode)
        : '';
    $minDecimal        = stripe_payment_format_smallest_unit($minSmallest, $currencyCode);

    $zeroDecimal = in_array($currencyCode, stripe_payment_zero_decimal_currencies(), true);
    $step        = $zeroDecimal ? '1' : '0.01';
@endphp

<div class="button-entrance stripe-payment-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="stripe-payment-{{ $link->id }}">
    {{-- Cancel deliberately excluded: a secondary action shouldn't
         take the accent recolor. --}}
    {!! block_appearance_style($link, ['id' => 'stripe-payment-' . $link->id, 'button' => ['.sp-option', '.sp-submit', '.sp-tip-confirm'], 'heading' => ['.sp-heading'], 'summary_id' => 'block-' . $link->id]) !!}
    <h3 class="sp-heading">{{ $link->title }}</h3>

    @if(session('stripe_payment_error') === (int) $link->id)
        <div class="sp-banner sp-error" role="alert">
            Checkout couldn't start. Please try again in a moment.
        </div>
    @endif

    @if($mode === 'tip_jar')
        {{-- TIP JAR: one button reveals an inline amount form --}}
        <button type="button" class="button button-default sp-submit sp-tip-open" data-target="sp-tip-form-{{ $link->id }}">
            <span class="sp-cta">{{ $link->link ?: 'Leave a tip' }}</span>
        </button>

        <form class="sp-form sp-tip-form" method="POST" action="{{ route('stripePaymentCheckout', ['id' => $link->id]) }}" id="sp-tip-form-{{ $link->id }}" style="display:none">
            @csrf
            <label for="sp-tip-amount-{{ $link->id }}" class="sp-label">How much would you like to give?</label>
            <div class="sp-amount-row">
                <span class="sp-currency-symbol">{{ $symbol }}</span>
                <input
                    type="number"
                    id="sp-tip-amount-{{ $link->id }}"
                    name="amount"
                    class="sp-amount-input"
                    min="{{ $minDecimal }}"
                    step="{{ $step }}"
                    value="{{ $suggestedDecimal }}"
                    placeholder="{{ $minDecimal }}"
                    required
                    inputmode="decimal">
                <span class="sp-currency-code">{{ $currencyUp }}</span>
            </div>
            <p class="sp-tip-note">Minimum {{ $symbol }}{{ $minDecimal }} {{ $currencyUp }}.</p>
            <div class="sp-tip-error" role="alert" style="display:none"></div>
            <div class="sp-tip-actions">
                <button type="button" class="button button-default sp-tip-cancel" data-target="sp-tip-form-{{ $link->id }}" data-opener="sp-tip-open-{{ $link->id }}">Cancel</button>
                <button type="submit" class="button button-default sp-tip-confirm">Continue to checkout</button>
            </div>
        </form>

        <p class="sp-trust">
            <i class="bi bi-shield-lock-fill"></i>
            Secure checkout powered by Stripe
        </p>

    @elseif(count($options) >= 2)
        {{-- FIXED PRICE, MULTIPLE OPTIONS: segmented button group --}}
        <form class="sp-form sp-options-form" method="POST" action="{{ route('stripePaymentCheckout', ['id' => $link->id]) }}">
            @csrf
            <div class="sp-option-group">
                @foreach($options as $i => $opt)
                    @php
                        $optAmount = stripe_payment_format_smallest_unit((int) ($opt['amount_cents'] ?? 0), $currencyCode);
                    @endphp
                    <button type="submit" name="option_index" value="{{ $i }}" class="button button-default sp-option">
                        <span class="sp-option-label">{{ !empty($opt['label']) ? $opt['label'] : 'Option ' . ($i + 1) }}</span>
                        <span class="sp-option-amount">{{ $symbol }}{{ $optAmount }} {{ $currencyUp }}</span>
                    </button>
                @endforeach
            </div>
            <p class="sp-trust">
                <i class="bi bi-shield-lock-fill"></i>
                Secure checkout powered by Stripe
            </p>
        </form>

    @else
        {{-- FIXED PRICE, SINGLE OPTION — original behaviour --}}
        @php
            $singleAmount = stripe_payment_format_smallest_unit(
                (int) ($options[0]['amount_cents'] ?? 0),
                $currencyCode
            );
        @endphp
        <form class="sp-form" method="POST" action="{{ route('stripePaymentCheckout', ['id' => $link->id]) }}">
            @csrf
            <button type="submit" class="button button-default sp-submit">
                <span class="sp-cta">{{ $link->link ?: 'Pay now' }}</span>
                <span class="sp-amount">{{ $symbol }}{{ $singleAmount }} {{ $currencyUp }}</span>
            </button>
            <p class="sp-trust">
                <i class="bi bi-shield-lock-fill"></i>
                Secure checkout powered by Stripe
            </p>
        </form>
    @endif
</div>

@if($mode === 'tip_jar')
<script nonce="{{ csp_nonce() }}">
(function () {
    var wrap = document.getElementById('stripe-payment-{{ $link->id }}');
    if (!wrap) return;
    var opener = wrap.querySelector('.sp-tip-open');
    var form   = document.getElementById('sp-tip-form-{{ $link->id }}');
    var cancel = wrap.querySelector('.sp-tip-cancel');
    var input  = form ? form.querySelector('input[name="amount"]') : null;
    var errBox = form ? form.querySelector('.sp-tip-error') : null;
    var minVal = {{ (float) $minDecimal }};
    if (!opener || !form || !input) return;

    opener.addEventListener('click', function () {
        opener.style.display = 'none';
        form.style.display   = '';
        setTimeout(function () { input.focus(); }, 10);
    });
    cancel.addEventListener('click', function () {
        form.style.display   = 'none';
        opener.style.display = '';
        if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }
    });

    form.addEventListener('submit', function (ev) {
        var entered = parseFloat(input.value);
        if (isNaN(entered) || entered < minVal) {
            ev.preventDefault();
            if (errBox) {
                errBox.textContent = 'Please enter at least {{ $symbol }}' + minVal.toFixed({{ $zeroDecimal ? 0 : 2 }}) + ' {{ $currencyUp }}.';
                errBox.style.display = '';
            }
            input.focus();
        }
    });
})();
</script>
@endif
