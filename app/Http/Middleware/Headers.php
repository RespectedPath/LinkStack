<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Headers
{
    public function handle(Request $request, Closure $next)
    {
        // Check if FORCE_HTTPS is set to true
        if (env('FORCE_HTTPS') == 'true') {
            \URL::forceScheme('https'); // Force HTTPS
        }

        // Check if FORCE_ROUTE_HTTPS is set to true
        if (env('FORCE_ROUTE_HTTPS') == 'true' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
            // Build the redirect host from the app's OWN configured URL,
            // not $_SERVER['HTTP_HOST'] — the Host header is attacker-
            // controlled, so the old code was an open redirect (Host:
            // evil.com -> Location: https://evil.com/...). If APP_URL
            // isn't configured we do NOT fall back to the request host;
            // continuing over the current scheme is safer than
            // redirecting to an untrusted destination.
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($appHost) {
                header('Location: https://' . $appHost . $request->getRequestUri());
                exit();
            }
        }

        $response = $next($request);

        // Some responses (streamed downloads, redirects handled above)
        // may not carry a headers bag we can set on; guard defensively.
        if (!method_exists($response, 'headers') && !isset($response->headers)) {
            return $response;
        }

        // ---- Baseline security headers (every response) ----
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Conservative CSP: locks down <base> injection and plugin/object
        // embedding without touching script/style. The app renders inline
        // <script>/<style> throughout, so a script-src policy would need
        // nonces threaded through every inline block — a larger, separate
        // hardening pass. upgrade-insecure-requests is added when HTTPS is
        // being forced so mixed content is auto-upgraded.
        $csp = "base-uri 'self'; object-src 'none'";
        if (env('FORCE_HTTPS') == 'true') {
            $csp .= '; upgrade-insecure-requests';
        }

        // Clickjacking protection on the AUTHENTICATED surface only.
        // Public bio pages stay frameable so customers can embed their
        // own page elsewhere; the studio / dashboard / admin (where a
        // framed click could trigger a state change) is pinned to
        // same-origin. Same-origin still covers the studio's own
        // live-preview and block-editor iframes.
        if ($request->is('studio', 'studio/*', 'dashboard', 'dashboard/*', 'admin', 'admin/*')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $csp .= "; frame-ancestors 'self'";
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
