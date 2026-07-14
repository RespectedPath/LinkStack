# Testing

The regression suite is the safety net for this fork. It exists because
manual click-testing kept finding bugs one at a time (theme switch
leaving an orphaned background image, silent block-save failures, …) —
every behavior we care about gets pinned as a test so it can't quietly
break again.

## Run it

```bash
php artisan test
```

Fast (~3s): fresh in-memory SQLite per run, full migrations + seeders.
No dev server, no real mail, no real Stripe — `phpunit.xml` forces the
array mail driver and a test-only SSO secret.

**Run the suite before every commit.** A red suite blocks the commit
until it's green or the pin is consciously updated.

## The rule: every bug becomes a test

When a bug is found — by a click-through, a customer, or an agent —
the fix ships WITH a test that fails on the old code. That's what makes
found-bugs permanent: the suite remembers so people don't have to.

## What's covered (tests/)

- `Feature/AppearanceThemeTest` — theme switch / reset / background
  upload+remove keep the sparse blob and the on-disk background file in
  lockstep (the bio renderer keys off file existence).
- `Feature/BlockSaveTest` — saveLink: stripe validation surfaces errors
  loudly, tip jar needs no price, option labels optional, zero-decimal
  currencies, pristine blocks never get styling frozen on, diverged
  blocks keep theirs.
- `Feature/DraftPublishTest` — public page renders the published
  snapshot; drafts stay private to the owner's ?preview=1.
- `Feature/SsoTest` — Mail Minted JWT handoff: valid logs in; expired /
  bad signature / wrong issuer / wrong audience / unknown user all land
  on /login unauthenticated.
- `Feature/ContactFormTest` — honeypot and timing-token both fake
  success and send nothing; valid submits mail the block owner; per-IP
  throttle 429s the sixth attempt.
- `Feature/StudioRoutesTest` — post-merge navigation (theme gallery
  lives on the Appearance pane, old routes redirect there).
- `Feature/HarnessSmokeTest` — the harness itself (migrations, seeders,
  auth, HTTP). If this fails, fix the harness before believing anything.
- `Unit/BlockHandlerTest` — block handlers (which all declare the same
  function name) load isolated per type via App\Services\BlockHandler.

## What the suite can NOT see

Browser-side behavior: inline-script CSP nonces, the block editor's
live JS (mode toggles, Customized badge), visual layout. Those are
verified in a real browser during development — when touching studio
JS, click the affected flow (or drive it with the preview tools) before
committing.

## Gotchas for test authors

- The background-image tests write real files into
  `assets/img/background-img/` — always use explicit user ids ≥ 990100
  (see AppearanceThemeTest) so a test can never delete a real account's
  background, and clean up in tearDown.
- `ConvertEmptyStringsToNull` runs in the HTTP pipeline: a posted `''`
  arrives as `null` in controllers. Tinker-invoking a controller
  directly skips middleware and will behave differently.
- The dev SQLite database predates some migrations (e.g. nullable
  custom_icon); the in-memory test schema is built fresh from
  migrations and is the truth. If dev disagrees with a test, suspect
  the dev DB, not the test.
