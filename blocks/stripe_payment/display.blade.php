<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

@php
  // Decode the (public-safe) config from type_params. Amount / currency /
  // description / URLs are all safe to render. The Stripe secret key
  // lives in .env and the connected account ID lives in users.stripe_account_id
  // — neither of those ever touches this template.
  $sp = json_decode($link->type_params ?? '{}', true);
  if (!is_array($sp)) { $sp = []; }
  $amountCents = (int) ($sp['amount_cents'] ?? 0);
  $currency    = strtoupper((string) ($sp['currency'] ?? 'USD'));
  $amountDisplay = number_format($amountCents / 100, 2);
@endphp

<div class="button-entrance stripe-payment-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="stripe-payment-{{ $link->id }}">
    <h3 class="sp-heading">{{ $link->title }}</h3>

    @if(session('stripe_payment_error') === (int) $link->id)
        <div class="sp-banner sp-error" role="alert">
            Checkout couldn't start. Please try again in a moment.
        </div>
    @endif

    <form class="sp-form" method="POST" action="{{ route('stripePaymentCheckout', ['id' => $link->id]) }}">
        @csrf

        <button type="submit" class="button button-default sp-submit">
            <span class="sp-cta">{{ $link->link ?: 'Pay now' }}</span>
            <span class="sp-amount">{{ $amountDisplay }} {{ $currency }}</span>
        </button>

        <p class="sp-trust">
            <i class="bi bi-shield-lock-fill"></i>
            Secure checkout powered by Stripe
        </p>
    </form>
</div>
