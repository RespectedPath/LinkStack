{{--
    Per-user Appearance overrides. Loaded after theme CSS so its rules
    cascade and win. Pushed from linkstack.blade.php into the
    linkstack-head-end stack. Emits nothing when the user has no
    overrides, so pure-theme rendering is untouched.

    Sparse model (THEME-APPEARANCE-PLAN.md Phase 2): the blob holds only
    the knobs the user changed off their theme; App\Services\
    AppearanceCss builds CSS for exactly those keys, pulling effective
    values for anything a rule needs from the theme's manifest. The
    same builder feeds the studio live preview (previewCss endpoint),
    so preview and public render can never drift.
--}}

@php
    $mmSparse   = App\Http\Controllers\AppearanceController::sparseForUser($userinfo ?? null);
    $mmManifest = App\Http\Controllers\AppearanceController::themeManifest($userinfo ?? null);
    $mmCss      = App\Services\AppearanceCss::build($mmSparse, $mmManifest);
    $mmFontHref = App\Services\AppearanceCss::fontHref($mmSparse);
@endphp

@if($mmCss !== '')
    @if($mmFontHref)
        {{-- Only the selected family is loaded, nothing else. --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="{{ $mmFontHref }}">
    @endif
    <style id="user-appearance-override">
{!! $mmCss !!}
    </style>
@endif
