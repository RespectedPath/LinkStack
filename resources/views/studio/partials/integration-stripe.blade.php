{{--
    Stripe Connect integration card.
    Consumes: $profile (User model) — reads stripe_account_id.
    Configured by StripeConnectController (connect/callback/disconnect).
--}}
<div class="card mb-3 integration-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-credit-card-2-front-fill text-primary me-1"></i>
                Payments (Stripe)
            </h5>
            @if(!empty($profile->stripe_account_id))
                <span class="badge bg-success">Connected</span>
            @else
                <span class="badge bg-secondary">Not connected</span>
            @endif
        </div>

        @if(!empty($profile->stripe_account_id))
            <p class="text-muted small mb-3">
                Payment blocks on your public page route payouts to
                <code>{{ $profile->stripe_account_id }}</code>. You keep 100% of each transaction minus Stripe's standard processing fee.
            </p>
            <form action="{{ route('stripe.disconnect') }}" method="post" onsubmit="return confirm('Disconnect this Stripe account? Payment blocks will stop working until you reconnect.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x-circle"></i> Disconnect Stripe
                </button>
            </form>
        @else
            <p class="text-muted small mb-3">
                Connect your Stripe account to accept payments on your public page. Zero platform fee; Stripe's standard processing fee still applies.
            </p>
            <a href="{{ route('stripe.connect') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-box-arrow-up-right"></i> Connect Stripe
            </a>
        @endif
    </div>
</div>
