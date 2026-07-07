{{--
    Google Analytics (GA4) gtag.js snippet.

    Requires: $trackingId — a GA4 measurement ID (G-XXXXXXXXXX). The
    caller includes this partial twice per public link page:
      - once with the platform-wide admin ID from .env
      - once with the page owner's per-user ID

    Renders nothing if the ID is empty / null. Never loads on admin
    panel pages because it's only pushed from linkstack.blade.php
    (studio/admin pages use the layouts/sidebar layout).
--}}
@if(!empty($trackingId))
<script nonce="{{ csp_nonce() }}" async src="https://www.googletagmanager.com/gtag/js?id={{ $trackingId }}"></script>
<script nonce="{{ csp_nonce() }}">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', @json($trackingId));
</script>
@endif
