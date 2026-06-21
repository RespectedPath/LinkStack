# Custom features in this LinkStack fork

Everything this fork adds on top of upstream LinkStack, with where to
configure each, the env vars that gate them, and the files worth
reading first when you come back to edit.

This is an operator-first runbook &mdash; click paths come before code paths.

---

## Contents

1. [Contact form block](#contact-form-block)
2. [Mailchimp signup block](#mailchimp-signup-block)
3. [Stripe Connect onboarding](#stripe-connect-onboarding)
4. [Stripe payment block (fixed price + tip jar)](#stripe-payment-block-fixed-price--tip-jar)
5. [Stripe webhook](#stripe-webhook)
6. [Google Analytics: platform + per-user](#google-analytics-platform--per-user)
7. [Temporary redirect](#temporary-redirect)
8. [Mail Minted integration points](#mail-minted-integration-points)
9. [Operator FAQ](#operator-faq)

---

## Contact form block

Renders a 3-field form (name / email / message) on the public page;
submissions email the page owner.

- **Configure**: Studio &rarr; *Add link* &rarr; **Contact form** &rarr; enter heading, destination email, optional custom subject.
- **Env vars** (SMTP is read from standard Laravel mail config):
  - `MAIL_MAILER=smtp`
  - `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`
- **Files**:
  - `blocks/contact_form/{config.yml, form.blade.php, handler.php, display.blade.php, styles.css}`
  - `app/Http/Controllers/ContactFormController.php`
  - `app/Mail/ContactFormMail.php`
  - `resources/views/layouts/contact-form-message.blade.php`
- **Route**: `POST /contact-form/{id}/submit` (throttle 5/min)
- **Spam protection**: honeypot field (inline-hidden) + server-side validation.

## Mailchimp signup block

Collects First/Last/Email on the public page and subscribes visitors to
the page owner's Mailchimp audience via the Marketing API v3.

- **Configure**: Studio &rarr; *Add link* &rarr; **Mailchimp signup** &rarr; paste API key + Audience List ID, set heading and CTA label.
- **API key + list ID** live only in `links.type_params` (server-side) &mdash; never rendered into HTML. Verified by the render test; see the comment at the top of `blocks/newsletter_signup/display.blade.php`.
- **Files**:
  - `blocks/newsletter_signup/{config.yml, form.blade.php, handler.php, display.blade.php, styles.css}`
  - `app/Http/Controllers/NewsletterSignupController.php`
- **Route**: `POST /newsletter/{id}/subscribe` (throttle 5/min)
- **Status handling**: new / already-subscribed / pending / unsubscribed / cleaned / API-error &mdash; all map to friendly banners with full detail logged to `storage/logs/laravel.log`.

## Stripe Connect onboarding

Each LinkStack user can OAuth-connect their own Stripe account so
payment blocks route funds to them.

- **Configure** (user): Studio &rarr; *Account Settings* &rarr; **Integrations** &rarr; click **Connect Stripe** &rarr; walk through Stripe's authorization.
- **Configure** (admin on Stripe side): enable **Standard OAuth** at <https://dashboard.stripe.com/test/settings/connect> and add `http://localhost:8000/stripe/connect/callback` under Redirect URIs (add whatever production host is in use too, once the app is deployed somewhere).
- **Env vars**:
  - `STRIPE_SECRET` (`sk_test_…` / `sk_live_…`)
  - `STRIPE_CONNECT_CLIENT_ID` (`ca_…`)
- **DB**: `users.stripe_account_id` (nullable string) &mdash; migration `2026_04_21_200139_add_stripe_account_id_to_users_table.php`.
- **Files**:
  - `app/Http/Controllers/StripeConnectController.php` &mdash; connect / callback / disconnect
  - `resources/views/studio/partials/integration-stripe.blade.php` &mdash; profile card
- **Routes**:
  - `GET /stripe/connect` &rarr; redirect to Stripe
  - `GET /stripe/connect/callback` &rarr; exchange code, store account ID
  - `POST /stripe/disconnect` &rarr; clear the account ID

## Stripe payment block (fixed price + tip jar)

Two modes in one block, selected at configure time:

- **Fixed price**: 1&ndash;3 preset amounts. Single option = single button (original behaviour). 2&ndash;3 options = segmented button group.
- **Tip jar**: visitor enters their own amount (min + suggested configurable).

- **Configure**: Studio &rarr; *Add link* &rarr; **Stripe payment** &rarr; pick mode, set options/tip floor, currency, description, success / cancel URLs.
- **Currency selector**: searchable (Select2) dropdown. USD/EUR/GBP/CAD/AUD pinned on top; remainder alphabetized. Zero-decimal currencies (JPY, KRW, etc.) handled correctly on both write and checkout. Canonical list in `blocks/stripe_payment/currencies.php`.
- **Zero platform fee**: `application_fee_amount: 0` is set explicitly in `StripePaymentController::checkout()`. Stripe's own processing fee still applies.
- **Files**:
  - `blocks/stripe_payment/{config.yml, form.blade.php, handler.php, display.blade.php, styles.css, currencies.php}`
  - `app/Http/Controllers/StripePaymentController.php`
- **Route**: `POST /stripe/checkout/{id}` (throttle 20/min)
- **Backward compatibility**: blocks saved before the multi-option / tip-jar expansion (only `amount_cents` in type_params) continue to render as a single-option fixed-price button. Controller synthesizes `options[0]` from the legacy field.

## Stripe webhook

Receives Stripe events. Signature-verified on every POST.

- **Configure**: Stripe Dashboard &rarr; *Developers* &rarr; *Webhooks* &rarr; add endpoint `https://your.domain/stripe/webhook` &rarr; listen for at least `payment_intent.succeeded`. Copy the signing secret.
- **Env vars**: `STRIPE_WEBHOOK_SECRET` (`whsec_…`)
- **Local dev**: use `stripe listen --forward-to http://localhost:8000/stripe/webhook` via Stripe CLI. The CLI prints a `whsec_…` &mdash; use that in local `.env`.
- **Files**: `app/Http/Controllers/StripeWebhookController.php`
- **CSRF**: `'stripe/webhook'` is listed in `app/Http/Middleware/VerifyCsrfToken.php` so POSTs aren't 419'd.
- **Route**: `POST /stripe/webhook`
- **Current handlers**: `payment_intent.succeeded` (logged); unhandled events ACK silently.

## Google Analytics: platform + per-user

Two GA4 trackers can fire independently on every public link page:

- **Platform-wide** (admin): `/admin/config` &rarr; **Google Analytics Tracking ID** &rarr; paste `G-XXXXXXXXXX`. Stored in `.env` as `GOOGLE_ANALYTICS_TRACKING_ID`.
- **Per-user**: Studio &rarr; *Account Settings* &rarr; **Integrations** &rarr; **Google Analytics**. Stored in `users.google_analytics_id`.
- Both, either, or neither can be configured; nothing renders when empty.
- **Admin pages never inject** because `layouts/sidebar.blade.php` is a different layout tree than the public `linkstack/layout.blade.php`.
- **Files**:
  - `resources/views/linkstack/modules/google-analytics.blade.php` &mdash; the gtag snippet, skipped when ID is empty
  - `resources/views/linkstack/linkstack.blade.php` &mdash; pushes the partial twice (admin env + user DB) into the `linkstack-head` stack
  - `resources/views/studio/partials/integration-analytics.blade.php` &mdash; profile card
- **Verification**: DevTools Network tab on `/@username` &rarr; expect requests to `gtag/js?id=G-…` and `google-analytics.com/g/collect`.

## Temporary redirect

Bypasses the link page entirely &mdash; sends every visitor to a URL of
the user's choosing until they turn it off. Links / blocks are preserved.

- **Configure**: Studio &rarr; *Account Settings* &rarr; **Integrations** &rarr; **Temporary redirect** &rarr; flip the switch and enter a URL.
- **Validation**: must begin with `http://` or `https://`. Invalid / empty URLs fall through to the normal page rather than 302ing to a broken target.
- **Implementation**: 302 is issued at the start of `UserController::littlelink()` and `littlelinkhome()` via `resolveTemporaryRedirect($userinfo)`. Happens before any page content is rendered.
- **DB**: `users.redirect_enabled` (boolean), `users.redirect_url` (text nullable) &mdash; migration `2026_04_22_020946_add_redirect_fields_to_users_table.php`.
- **Files**:
  - `app/Http/Controllers/UserController.php` &rarr; `littlelink` / `littlelinkhome` / `resolveTemporaryRedirect` / `editRedirect`
  - `resources/views/studio/partials/integration-redirect.blade.php` &mdash; profile card
- **Route**: `POST /studio/profile/redirect`

## Collapsible blocks

Per-block-instance "Start collapsed" toggle for any `custom_html` block (contact form, Mailchimp, Stripe payment, Appearance editor's per-user customisation aside). Visitor sees the block's heading text as a clickable bar with a chevron; clicking expands the form/content. Native `<details>/<summary>` &mdash; no JS, accessible by default.

- **Configure**: Studio &rarr; edit any contact / Mailchimp / Stripe payment block &rarr; bottom of the form &rarr; **Start collapsed** switch.
- **Public page**: when set, the block renders as a `<details>` wrapper around the existing `display.blade.php` include. The block's own H3 heading is suppressed via CSS so the summary text and inner heading don't duplicate.
- **Fragment auto-open**: browsers automatically open a `<details>` whose subtree contains the URL fragment, so the contact form's `back()->withFragment("contact-form-$id")` redirect after a validation error continues to work &mdash; the details opens and the error is visible.
- **Storage**: `collapsed` boolean inside the existing `links.type_params` JSON. No schema change.
- **Files**:
  - `resources/views/studio/partials/block-collapsed-toggle.blade.php` &mdash; shared toggle partial
  - `resources/views/linkstack/elements/buttons.blade.php` &mdash; wraps the custom_html include in `<details>` when `$link->collapsed` is set; conditionally emits the accordion CSS only when at least one collapsed block exists on the page
  - Each block's `form.blade.php` &mdash; appends `@include('studio.partials.block-collapsed-toggle')`
  - Each block's `handler.php` &mdash; adds `'collapsed' => (bool) $request->input('collapsed')` to `$linkData`

## Mail Minted integration points

Mail Minted (the SaaS platform this fork serves) hits LinkStack through:

- **Admin API**: `routes/api-admin.php` &mdash; server-to-server user provisioning. Bearer-auth via `LINKSTACK_ADMIN_API_TOKEN`.
  - `POST /api/admin/users` &mdash; create user
  - `PATCH /api/admin/users/{userId}` &mdash; update user
  - `DELETE /api/admin/users/{userId}` &mdash; soft/hard delete
- **SSO bridge**: `routes/sso-mailminted.php` + `deploy/linkstack/sso-mailminted.php` &mdash; Mail Minted issues a 60-second JWT, LinkStack validates + `Auth::loginUsingId`.
  - Shared secret: `MAILMINTED_SSO_SHARED_SECRET`
- **Custom domain mapping**: `users.custom_domain` column &mdash; migration `2026_04_18_235443_add_custom_domain_to_users.php`. A customer's apex (e.g. `janesmith.com`) maps to a single LinkStack user.

## Operator FAQ

**"I toggled a setting but nothing happened."** &mdash; LinkStack caches Blade views and config. Run:
```
cd ~/dev/LinkStack
php artisan view:clear
php artisan config:clear
```
Restart the dev server if `.env` changed.

**"My `.env` edit isn't being read."** &mdash; Laravel doesn't re-read `.env` on every request if config is cached. `php artisan config:clear` removes any cached config and `php artisan serve` will pick up `.env` changes fresh.

**"I want to test with a sandbox Stripe account."** &mdash; Dashboard &rarr; test mode toggle (top right). All Stripe env vars in this fork accept test (`sk_test_…`, `ca_test_…`, `whsec_…` from a test-mode webhook) or live keys &mdash; the code is agnostic.

**"Where does this run in production?"** &mdash; It doesn't, currently. The project is local-only for now &mdash; develop with `php artisan serve`, commit to GitHub for safekeeping, deploy somewhere later (any Docker host works: Fly.io, Hetzner, DigitalOcean App Platform, etc. &mdash; the upstream `linkstackorg/linkstack` image is the deployment target). The previous Railway deployment was retired.

**"How do I remove the 'Powered by LinkStack' badge before launching?"** &mdash; Set `DISPLAY_CREDIT=false` in `.env`. The badge SVG (`assets/linkstack/images/powered-by-linkstack.svg`) and its link to `linkstack.org` live in `resources/views/linkstack/modules/footer.blade.php`. The companion `DISPLAY_FOOTER` toggle controls the Home / Terms / Privacy / Contact link row above it. License is AGPL-3.0 &mdash; source availability is required for network users, but visible UI attribution is not.

**"My local `.env` has secrets but `.env` is in git?"** &mdash; LinkStack tracks a default `.env` template (with blank secret fields). Your local `.env` is `git update-index --assume-unchanged` so your Purelymail / Stripe / Mailchimp creds don't leak. See `git ls-files -v | grep '^[a-z]'` to list assume-unchanged files.

---

## Where to change things

| You want to change... | File(s) |
|---|---|
| A custom block's fields (admin form) | `blocks/<type>/form.blade.php` |
| A custom block's admin-side validation | `blocks/<type>/handler.php` |
| A custom block's public rendering | `blocks/<type>/display.blade.php` |
| A custom block's styles | `blocks/<type>/styles.css` |
| A block's submission logic | `app/Http/Controllers/<Type>Controller.php` |
| Profile-page integration cards | `resources/views/studio/partials/integration-*.blade.php` |
| Routes for public form submissions | `routes/web.php` (search for "Custom block: public form submissions") |
| Stripe Connect routes | `routes/web.php` (search for "Stripe Connect OAuth onboarding") |
| Webhook CSRF exception | `app/Http/Middleware/VerifyCsrfToken.php` |
| Admin-level GA tracking ID | `/admin/config` UI, backed by `.env`'s `GOOGLE_ANALYTICS_TRACKING_ID` |
| Per-user GA tracking ID | `/studio/profile` UI, backed by `users.google_analytics_id` |
