<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Headers
{
    public function handle(Request $request, Closure $next)
    {
        // Per-request CSP nonce — generated BEFORE the view renders so
        // inline <script nonce="{{ csp_nonce() }}"> can pick it up. Read
        // back via the csp_nonce() helper (request attribute).
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

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

        // Enforced baseline (every response): <base> injection + plugin
        // embedding. upgrade-insecure-requests when HTTPS is forced.
        $baseCsp = "base-uri 'self'; object-src 'none'";
        if (env('FORCE_HTTPS') == 'true') {
            $baseCsp .= '; upgrade-insecure-requests';
        }

        // The script + supporting directives (CSP-HARDENING-PLAN.md
        // Phase 0/1). script-src is the TIGHT one — 'self' + this
        // request's nonce + the GA origin — so an injected inline
        // <script> without the nonce won't run. The supporting
        // directives are present but permissive-https so media embeds,
        // fonts, images and GA keep working; the XSS win is script-src,
        // not locking down images. style-src keeps 'unsafe-inline' (the
        // app renders inline styles everywhere; nonces don't apply to
        // style attributes; style injection is low risk).
        $scriptCsp = implode('; ', [
            "script-src 'self' 'nonce-{$nonce}' https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https:",
            "img-src 'self' data: https:",
            "font-src 'self' data: https:",
            "frame-src https:",
            "connect-src 'self' https:",
        ]);

        // The public bio page (UserController@littlelink / littlelinkhome)
        // is the only surface we ENFORCE script-src on today; it's the
        // stored-XSS target and all its inline scripts are nonced. It
        // stays frameable (no frame-ancestors) so customers can embed it.
        $action = optional($request->route())->getActionName();
        $isBioPage = is_string($action) && str_contains($action, 'littlelink');

        $isAuthArea = $request->is('studio', 'studio/*', 'dashboard', 'dashboard/*', 'admin', 'admin/*');

        if ($isBioPage) {
            $response->headers->set(
                'Content-Security-Policy',
                $baseCsp . '; ' . $scriptCsp . '; report-uri /csp-report'
            );
        } else {
            // Everywhere else: keep the enforced baseline (+ clickjacking
            // on the authenticated surface), and ship script-src as
            // REPORT-ONLY so Phase 2 can measure the studio without
            // breaking it.
            $enforced = $baseCsp;
            if ($isAuthArea) {
                $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
                $enforced .= "; frame-ancestors 'self'";
            }
            $response->headers->set('Content-Security-Policy', $enforced);
            $response->headers->set(
                'Content-Security-Policy-Report-Only',
                $scriptCsp . '; report-uri /csp-report'
            );
        }

        return $response;
    }
}
