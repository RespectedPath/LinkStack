{{--
    Stripe Connect integration card (Settings → Integrations).

    The connection is account-level — one Stripe account serves every
    payment block on the page — so this card and the in-block panel both
    render the shared studio.partials.stripe-connect-panel and behave
    identically (same popup connect, same disconnect). Managing it here
    or from a block affects all payment blocks the same way.
--}}
<div class="card mb-3 integration-card">
    <div class="card-body">
        <h5 class="mb-3">
            <i class="bi bi-credit-card-2-front-fill text-primary me-1"></i>
            Payments (Stripe)
        </h5>
        @include('studio.partials.stripe-connect-panel')
    </div>
</div>
