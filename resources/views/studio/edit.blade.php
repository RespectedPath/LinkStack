@extends('layouts.sidebar')

@section('content')

{{-- Shared front-end deps for the unified editor, loaded once:
     - Font Awesome (brand glyphs) for the Social + Blocks tabs. JS
       variant, not CSS, to avoid the 404-ing relative webfont paths
       (same reason social-icons.blade used it).
     - appearance.css for the Appearance tab's controls + the shared
       .appearance-layout grid that splits tab content from preview. --}}
@push('sidebar-stylesheets')
<script nonce="{{ csp_nonce() }}" defer src="{{ asset('assets/external-dependencies/fontawesome.js') }}" crossorigin="anonymous"></script>
<link rel="stylesheet" href="{{ asset('assets/css/appearance.css') }}">
@endpush

<style>
    /* ===== Unified studio editor — top-level tab chrome ===== */
    .mm-edit-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        border-bottom: 1px solid rgba(128, 128, 128, 0.2);
        margin-bottom: 18px;
        padding-bottom: 0;
    }
    .mm-edit-tab {
        appearance: none;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 18px;
        font-size: 0.98rem;
        font-weight: 600;
        color: inherit;
        opacity: 0.6;
        cursor: pointer;
        transition: opacity 0.12s ease, border-color 0.12s ease;
    }
    .mm-edit-tab:hover { opacity: 0.9; }
    .mm-edit-tab.active {
        opacity: 1;
        border-bottom-color: var(--bs-primary, #3b82f6);
    }
    .mm-edit-tab .bi { margin-right: 6px; }

    .mm-pane { display: none; }
    .mm-pane.active { display: block; }

    /* Tab content sits in the left grid cell; the live preview the
       shell includes fills the right cell. Reuses .appearance-layout
       (1fr 1fr, stacks <=992px) so the split matches the old pages. */
    .mm-edit-content { min-width: 0; }

    /* Draft/publish status bar */
    .mm-publish-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 10px 14px;
        margin-bottom: 16px;
        border: 1px solid rgba(128, 128, 128, 0.25);
        border-radius: 8px;
        background: rgba(128, 128, 128, 0.06);
        font-size: 0.92rem;
    }
    .mm-publish-bar--dirty {
        border-color: var(--bs-primary, #3b82f6);
        background: rgba(59, 130, 246, 0.08);
    }
    .mm-publish-status { display: inline-flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .mm-publish-actions { display: flex; gap: 8px; }
    .mm-publish-bar .btn[disabled] { opacity: 0.5; }

    /* Client-toggled dirty state: both status messages live in the DOM;
       the --dirty class picks which shows and reveals the Discard form. */
    .mm-publish-bar .mm-publish-status-dirty { display: none; }
    .mm-publish-bar--dirty .mm-publish-status-dirty { display: inline; }
    .mm-publish-bar--dirty .mm-publish-status-clean { display: none; }
    .mm-publish-bar:not(.mm-publish-bar--dirty) .mm-discard-form { display: none; }
    .mm-save-indicator { margin-left: 8px; opacity: 0.7; font-size: 0.85rem; font-style: italic; }
    .mm-save-indicator--error { color: var(--bs-danger, #dc3545); opacity: 1; font-style: normal; }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
  <div class="row">
    <div class="col-12">
      <div class="card rounded">
        <div class="card-body">

          {{-- Draft/publish status bar. Your edits auto-save to your DRAFT
               as you make them and show in the live preview; the public
               page only changes when you Publish. The dirty state is
               toggled client-side after each auto-save (script below). --}}
          <div class="mm-publish-bar @if(!empty($isDirty)) mm-publish-bar--dirty @endif" id="mm-publish-bar">
            <span class="mm-publish-status">
              <span class="mm-publish-status-dirty"><i class="bi bi-dot"></i> You have <strong>unpublished changes</strong> &mdash; visible here, not yet on your public page.</span>
              <span class="mm-publish-status-clean"><i class="bi bi-check-circle"></i> Your public page is up to date.</span>
              <span class="mm-save-indicator" id="mm-save-indicator" aria-live="polite"></span>
            </span>
            <div class="mm-publish-actions">
              <form action="{{ route('discard') }}" method="post" class="mb-0 mm-discard-form">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm" data-confirm="Discard your unpublished changes and revert to your published page?">
                  <i class="bi bi-arrow-counterclockwise"></i> Discard
                </button>
              </form>
              <form action="{{ route('publish') }}" method="post" class="mb-0">
                @csrf
                <button type="submit" id="mm-publish-btn" class="btn btn-primary btn-sm" @if(empty($isDirty)) disabled @endif>
                  <i class="bi bi-cloud-arrow-up"></i> Publish
                </button>
              </form>
            </div>
          </div>

          {{-- Shared flash + validation surface. Every tab's form
               redirects back here (old GET routes now point at this
               page), so a single alert block serves them all. --}}
          @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger">
              <strong>Couldn't save:</strong>
              <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
              </ul>
            </div>
          @endif

          {{-- ===== Top-level tabs ===== --}}
          <nav class="mm-edit-tabs" role="tablist" aria-label="Page editor sections">
            <button type="button" class="mm-edit-tab" data-mm-tab="basics"     role="tab"><i class="bi bi-person-vcard"></i> Basics</button>
            <button type="button" class="mm-edit-tab" data-mm-tab="appearance" role="tab"><i class="bi bi-palette-fill"></i> Appearance</button>
            <button type="button" class="mm-edit-tab" data-mm-tab="social"     role="tab"><i class="bi bi-share-fill"></i> Social</button>
            <button type="button" class="mm-edit-tab" data-mm-tab="blocks"     role="tab"><i class="bi bi-link-45deg"></i> Blocks</button>
          </nav>

          {{-- ===== Grid: tab content | shared live preview ===== --}}
          <div class="appearance-layout">
            <div class="mm-edit-content">
              <div class="mm-pane" id="pane-basics"     role="tabpanel">@include('studio.partials.edit.basics')</div>
              {{-- One styling home (Linktree layout): the theme gallery sits at
                   the top of the Appearance pane, the fine-tuning controls below.
                   The old standalone Themes tab is gone; #themes deep-links are
                   aliased to this pane in the tab JS. --}}
              <div class="mm-pane" id="pane-appearance" role="tabpanel">
                @include('studio.partials.edit.themes')
                @include('studio.partials.edit.appearance')
              </div>
              <div class="mm-pane" id="pane-social"     role="tabpanel">@include('studio.partials.edit.social')</div>
              <div class="mm-pane" id="pane-blocks"     role="tabpanel">@include('studio.partials.edit.blocks')</div>
            </div>

            @include('studio.partials.live-preview', ['littleLinkName' => $user->littlelink_name ?? null])
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- appearance.js drives the Appearance tab (photo cropper, bg upload,
     swatch state, reset). Loaded once at body end. --}}
@push('sidebar-scripts')
<script nonce="{{ csp_nonce() }}" src="{{ asset('assets/js/mm-image-resize.js') }}?v={{ filemtime(public_path('assets/js/mm-image-resize.js')) }}"></script>
<script nonce="{{ csp_nonce() }}" src="{{ asset('assets/js/appearance.js') }}?v={{ filemtime(public_path('assets/js/appearance.js')) }}"></script>
<script nonce="{{ csp_nonce() }}">
(function () {
    var VALID = ['basics', 'appearance', 'social', 'blocks'];
    var tabs  = Array.prototype.slice.call(document.querySelectorAll('.mm-edit-tab'));
    var panes = {};
    VALID.forEach(function (t) { panes[t] = document.getElementById('pane-' + t); });

    function activate(name, push) {
        // The theme gallery merged into the Appearance pane; old #themes
        // bookmarks, redirects, and in-page links land there.
        if (name === 'themes') name = 'appearance';
        if (VALID.indexOf(name) === -1) name = 'basics';
        tabs.forEach(function (btn) {
            var on = btn.getAttribute('data-mm-tab') === name;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        VALID.forEach(function (t) {
            if (panes[t]) panes[t].classList.toggle('active', t === name);
        });
        if (push && location.hash.replace('#', '') !== name) {
            history.replaceState(null, '', '#' + name);
        }
    }

    // Tab button clicks.
    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activate(btn.getAttribute('data-mm-tab'), true);
        });
    });

    // In-page cross-links (e.g. "Profile photo lives on the Appearance
    // tab") carry data-mm-tab and switch tabs instead of navigating.
    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-mm-tab]');
        if (!link) return;
        e.preventDefault();
        activate(link.getAttribute('data-mm-tab'), true);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Respond to hash changes (back/forward, or a redirect landing on
    // /studio/edit#appearance from an old bookmarked URL).
    window.addEventListener('hashchange', function () {
        activate(location.hash.replace('#', ''), false);
    });

    // Initial tab from the URL hash, default Basics.
    activate(location.hash.replace('#', '') || 'basics', false);
})();
</script>
<script nonce="{{ csp_nonce() }}">
(function () {
    // ---- Draft auto-save wiring ----------------------------------------
    // Tabs persist edits to the draft by calling window.mmAutoSaveForm();
    // on success it flips the banner to "unpublished changes" and enables
    // Publish. No manual Save step, no unsaved-changes warning.
    var bar        = document.getElementById('mm-publish-bar');
    var publishBtn = document.getElementById('mm-publish-btn');
    var indicator  = document.getElementById('mm-save-indicator');
    var indicatorTimer = null;

    window.mmMarkDirty = function () {
        if (bar) bar.classList.add('mm-publish-bar--dirty');
        if (publishBtn) publishBtn.disabled = false;
    };

    window.mmSaveStatus = function (state) {
        if (!indicator) return;
        clearTimeout(indicatorTimer);
        indicator.classList.toggle('mm-save-indicator--error', state === 'error');
        if (state === 'saving') {
            indicator.textContent = 'Saving…';
        } else if (state === 'saved') {
            indicator.textContent = 'Saved';
            indicatorTimer = setTimeout(function () { indicator.textContent = ''; }, 1500);
        } else if (state === 'error') {
            indicator.textContent = "Couldn't save — check your connection";
        } else {
            indicator.textContent = '';
        }
    };

    // Debounced per-form auto-save. POSTs the form to its own action; the
    // server persists to the draft and (harmlessly) redirects, which we
    // ignore. One in-flight timer per form.
    var timers = new WeakMap();
    window.mmAutoSaveForm = function (form, delay) {
        if (!form) return;
        clearTimeout(timers.get(form));
        timers.set(form, setTimeout(function () {
            window.mmSaveStatus('saving');
            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function (r) {
                if (!r.ok) throw new Error('save failed ' + r.status);
                window.mmMarkDirty();
                window.mmSaveStatus('saved');
            }).catch(function () {
                window.mmSaveStatus('error');
            });
        }, delay || 700));
    };
})();
</script>
@endpush

@endsection
