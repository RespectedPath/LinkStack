{{--
    "edited" chip — shown when the given appearance knob(s) differ from
    the active theme, so the user can see where they've diverged.

    $keys: dot-key string ("buttons.shape") or several space-separated /
    array — any match shows the chip. Reads $sparseAppearance from the
    including scope (showEditor provides it) for the initial state;
    appearance.js live-toggles via [data-mm-edited-key] using the keys
    list the preview-css endpoint returns on every edit.
--}}
@php
    $mmBadgeKeys = is_array($keys) ? $keys : preg_split('/\s+/', trim($keys));
    $mmBadgeOn = collect($mmBadgeKeys)->contains(fn ($k) => data_get($sparseAppearance ?? [], $k) !== null);
@endphp
<span class="badge bg-soft-primary mm-edited-badge"
      data-mm-edited-key="{{ implode(' ', $mmBadgeKeys) }}"
      title="Changed from your theme — Reset to theme brings it back"
      @unless($mmBadgeOn) style="display:none" @endunless>edited</span>
