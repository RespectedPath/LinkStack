<?php
  // Defaults pre-fill sensible Stripe-friendly values for a new block.
  $defaultSuccessUrl = url('/@' . (auth()->user()->littlelink_name ?? ''));
  $defaultCancelUrl  = $defaultSuccessUrl;
?>

<select style="display:none" name="button" class="form-control"><option class="button button-default stripe_payment" value="default stripe_payment">Stripe payment</option></select>

<label for='title' class='form-label'>Section heading</label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Pay me" required />
<span class='small text-muted'>Text shown above the payment button</span><br>

<label for='link' class='form-label'>Button label</label>
<input type='text' name='link' value='{{ $link ?? '' }}' class='form-control' maxlength="50" placeholder="Pay now" required />
<span class='small text-muted'>Text on the payment button. The amount will be shown beneath it automatically.</span><br>

<label for='amount' class='form-label'>Amount</label>
<input type='number' name='amount' value='{{ isset($amount_cents) ? number_format($amount_cents / 100, 2, '.', '') : '' }}' class='form-control' min="0.50" step="0.01" required placeholder="5.00" />
<span class='small text-muted'>The amount to charge, in the currency below. Minimum 0.50.</span><br>

<label for='currency' class='form-label'>Currency</label>
<input type='text' name='currency' value='{{ $currency ?? 'usd' }}' class='form-control' maxlength="3" pattern="[a-zA-Z]{3}" required placeholder="usd" />
<span class='small text-muted'>Three-letter ISO currency code (e.g. <code>usd</code>, <code>eur</code>, <code>gbp</code>). See Stripe's supported currencies.</span><br>

<label for='product_description' class='form-label'>Product description</label>
<input type='text' name='product_description' value='{{ $product_description ?? '' }}' class='form-control' maxlength="200" required placeholder="One coffee for James" />
<span class='small text-muted'>Shown to the customer on Stripe's checkout page</span><br>

<label for='success_url' class='form-label'>Success redirect URL</label>
<input type='url' name='success_url' value='{{ $success_url ?? $defaultSuccessUrl }}' class='form-control' maxlength="500" required />
<span class='small text-muted'>Where to send the customer after a successful payment</span><br>

<label for='cancel_url' class='form-label'>Cancel redirect URL</label>
<input type='url' name='cancel_url' value='{{ $cancel_url ?? $defaultCancelUrl }}' class='form-control' maxlength="500" required />
<span class='small text-muted'>Where to send the customer if they cancel at checkout</span>
