{{--
    Google Analytics integration card.
    Consumes: $profile (User model) — reads google_analytics_id.
    Form posts to editAnalytics route (per-user GA ID, validated + stored).
    Platform-wide GA ID is separate (admin /admin/config).
--}}
<div class="card mb-3 integration-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="bi bi-graph-up-arrow text-primary me-1"></i>
                Google Analytics
            </h5>
            @if(!empty($profile->google_analytics_id))
                <span class="badge bg-success">Tracking</span>
            @else
                <span class="badge bg-secondary">Not set</span>
            @endif
        </div>

        <p class="text-muted small mb-3">
            Your GA4 measurement ID, loaded on your public page. Format: <code>G-XXXXXXXXXX</code>. Leave blank to disable.
        </p>

        <form action="{{ route('editAnalytics') }}" method="post" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-8">
                <input type="text" class="form-control" name="google_analytics_id" value="{{ $profile->google_analytics_id }}" placeholder="G-XXXXXXXXXX" maxlength="30" pattern="^(G-[A-Z0-9]+)?$">
            </div>
            <div class="col-sm-4">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-save"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
