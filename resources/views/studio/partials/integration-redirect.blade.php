{{--
    Temporary redirect integration card.
    Consumes: $profile (User model) — reads redirect_enabled + redirect_url.
    Form posts to editRedirect route. When enabled, the public link page
    302s visitors to redirect_url instead of rendering.
--}}
<div class="card mb-3 integration-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-arrow-right-circle text-primary me-1"></i>
                Temporary redirect
            </h5>
            @if($profile->redirect_enabled && !empty($profile->redirect_url))
                <span class="badge bg-warning text-dark">Redirect active</span>
            @elseif($profile->redirect_enabled)
                <span class="badge bg-warning text-dark">Enabled (no URL set)</span>
            @else
                <span class="badge bg-secondary">Off</span>
            @endif
        </div>

        <p class="text-muted small mb-3">
            When enabled, visitors are sent straight to the URL below instead of seeing your links. Your links and blocks stay configured &mdash; turning this off returns your page to normal.
        </p>

        <form action="{{ route('editRedirect') }}" method="post">
            @csrf
            {{-- Hidden default so the field is always submitted, even when the checkbox is unchecked. --}}
            <input type="hidden" name="redirect_enabled" value="0">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="redirect-toggle" name="redirect_enabled" value="1" @if($profile->redirect_enabled) checked @endif>
                <label class="form-check-label" for="redirect-toggle">Send all visitors to a URL</label>
            </div>

            <div id="redirect-url-wrap" @if(!$profile->redirect_enabled) style="display:none" @endif>
                <label for="redirect-url" class="form-label small">Destination URL</label>
                <input type="url" class="form-control" id="redirect-url" name="redirect_url" value="{{ $profile->redirect_url }}" placeholder="https://example.com" maxlength="2048" pattern="https?://.+">
                <div class="alert alert-warning mt-2 mb-3 small">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Your link page will not be visible to visitors while this is active.
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-save"></i> Save redirect settings
            </button>
        </form>
        <script>
            (function () {
                var cb = document.getElementById('redirect-toggle');
                var wrap = document.getElementById('redirect-url-wrap');
                if (!cb || !wrap) return;
                cb.addEventListener('change', function () { wrap.style.display = cb.checked ? '' : 'none'; });
            })();
        </script>
    </div>
</div>
