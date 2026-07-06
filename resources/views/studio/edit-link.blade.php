@extends('layouts.sidebar')

@section('content')

@push('sidebar-stylesheets')
<script src="{{ asset('assets/external-dependencies/fontawesome.js') }}" crossorigin="anonymous"></script>
@if(!empty($embed))
{{-- Embed mode: this page is loaded in an iframe panel inside the
     unified editor's Blocks tab. Strip the dashboard chrome (sidebar,
     top navbar, banner header, footer) so only the editor body shows,
     and undo the negative top margin that normally slides content under
     the banner. The .mm-embed class is set on <body> by the layout when
     ?embed=1 is present. --}}
<style>
  body.mm-embed .sidebar,
  body.mm-embed .iq-navbar,
  body.mm-embed .iq-navbar-header,
  body.mm-embed footer.footer { display: none !important; }
  body.mm-embed .main-content { margin-left: 0 !important; }
  body.mm-embed .content-inner.mt-n5 { margin-top: 0 !important; }
  body.mm-embed { background: transparent !important; }
  body.mm-embed .card { box-shadow: none !important; border: none !important; }
  body.mm-embed .card-body { padding-top: 8px !important; }
</style>
@endif
<style>
    /* Pass 3 — unified block-edit page. Three sections (Content,
       Appearance, Settings) live inside one form so the operator
       configures a block in one place instead of bouncing between
       /studio/edit-link and the legacy /studio/button-editor. */
    .mm-edit-section {
        border: 1px solid rgba(128, 128, 128, 0.2);
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 18px;
    }
    .mm-edit-section legend {
        font-size: 1.05rem;
        font-weight: 600;
        padding: 0 6px;
        margin-bottom: 0;
        width: auto;
    }
    .mm-edit-back {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 10px;
        opacity: 0.75;
    }
    .mm-edit-back:hover { opacity: 1; }
    .mm-css-textarea {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.85rem;
        min-height: 140px;
    }

    /* ===== Appearance section — visual controls ===== */

    /* Live preview button — sticky at the top of the section so the
       user always sees their changes while scrolling through controls
       below. */
    .mm-preview-wrap {
        position: sticky;
        top: 12px;
        margin: -8px -12px 18px;
        padding: 12px;
        background: rgba(128,128,128,0.06);
        border: 1px dashed rgba(128,128,128,0.25);
        border-radius: 10px;
        z-index: 5;
    }
    .mm-preview-label {
        display: block;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .mm-preview-stage {
        display: flex;
        justify-content: center;
        padding: 8px;
    }
    .mm-preview-btn {
        /* Sensible defaults — the JS overrides these inline based on
           the current state. All visible defaults match the "Filled"
           preset so the initial preview isn't a flash of un-styled
           text. */
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 500;
        cursor: default;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        background: #3b82f6;
        color: #ffffff;
        border-radius: 16px;
        transition: all 0.18s ease;
        min-width: 200px;
        justify-content: center;
    }

    /* Section labels */
    .mm-control-group { margin-top: 16px; }
    .mm-control-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        opacity: 0.85;
    }

    /* Preset gallery */
    .mm-preset-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 8px;
    }
    .mm-preset {
        background: transparent;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 8px 6px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        transition: all 0.12s ease;
    }
    .mm-preset:hover { background: rgba(128,128,128,0.08); }
    .mm-preset.active { border-color: #3b82f6; background: rgba(59,130,246,0.08); }
    .mm-preset-swatch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 32px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .mm-preset-name {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    /* Shape chip row */
    .mm-chip-row {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .mm-chip {
        padding: 6px 14px;
        font-size: 0.85rem;
        background: transparent;
        border: 1px solid rgba(128,128,128,0.3);
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .mm-chip:hover { background: rgba(128,128,128,0.08); }
    .mm-chip.active {
        background: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
    }

    /* Icon grid */
    .mm-icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(44px, 1fr));
        gap: 6px;
    }
    .mm-icon-tile {
        aspect-ratio: 1;
        background: rgba(128,128,128,0.08);
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.12s ease;
    }
    .mm-icon-tile:hover { background: rgba(128,128,128,0.16); }
    .mm-icon-tile.active {
        border-color: #3b82f6;
        background: rgba(59,130,246,0.12);
    }

    /* Color inputs full-width */
    .form-control-color {
        height: 40px;
        padding: 4px;
    }

    /* Secondary color picker is hidden by default. The JS toggles
       data-treatment on .mm-appearance to "gradient" when the
       Gradient preset is active, which reveals the secondary
       color picker. */
    .mm-secondary-wrap { display: none; }
    .mm-appearance[data-treatment="gradient"] .mm-secondary-wrap { display: block; }

    /* Preset swatches — each one renders a mini preview of its
       treatment so the user can SEE the difference without
       clicking through. Colors here are illustrative; the actual
       button uses the user's chosen primary/text/secondary. */
    .mm-preset-swatch {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 32px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .mm-swatch-filled    { background: #3b82f6; color: #fff; border-radius: 8px; }
    .mm-swatch-outlined  { background: transparent; color: #3b82f6; border: 2px solid #3b82f6; border-radius: 8px; }
    .mm-swatch-gradient  { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border-radius: 12px; }
    .mm-swatch-soft      { background: #dbeafe; color: #1e40af; border-radius: 12px; }
    .mm-swatch-glass     {
        background: rgba(255,255,255,0.18);
        background-image: linear-gradient(135deg, #cbd5e1, #94a3b8);
        color: #0f172a;
        border: 1px solid rgba(255,255,255,0.4);
        backdrop-filter: blur(10px);
        border-radius: 12px;
    }
    .mm-swatch-neon      { background: #0a0a0a; color: #22ff88; border-radius: 8px; box-shadow: 0 0 12px #22ff88; }
    .mm-swatch-ghost     { background: transparent; color: #475569; border-radius: 8px; }

    /* Advanced expander — muted, low-emphasis. Operator shouldn't
       feel like they SHOULD click it. */
    .mm-advanced summary { padding: 4px 0; }
</style>
@endpush

<div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row">
     <div class="col-lg-12">
        <div class="card rounded">
            <div class="card-body">
               <div class="row">
                   <div class="col-sm-12">

                    @push('sidebar-stylesheets')
                    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
                    @endpush

                    @if($LinkID === 0)
                        {{-- ====================================================
                             ADD MODE: page is a grid of block-type tiles.
                             Each tile opens a modal containing that block's
                             form. A new block (separate row in the `links`
                             table) is created each time the user saves —
                             which is how multiple Mailchimp / contact form /
                             Stripe payment blocks coexist on one page.

                             Per-block appearance customization happens on the
                             edit page AFTER the block is saved — keeps the
                             create modal focused on the type-specific form.
                             ==================================================== --}}
                        <a href="{{ url('studio/links') }}" class="mm-edit-back"><i class="bi bi-arrow-left"></i> Back to Blocks</a>
                        <section class='text-gray-400'>
                            <h3 class="mb-4 card-header"><i class="bi bi-journal-plus"></i> {{__('messages.Add')}} {{__('messages.Block')}}</h3>
                            <div class='card-body'>
                                <p class="text-muted mb-3">Choose what kind of block to add to your page. You can add as many of each type as you want &mdash; each one is independent.</p>

                                <div id="blockTypeGrid" class="p-0">
                                    @php
                                        $grouped = collect($LinkTypes)->groupBy('category');
                                    @endphp

                                    @foreach (\App\Models\LinkType::CATEGORY_ORDER as $catKey)
                                        @php
                                            $tiles = $grouped->get($catKey, collect());
                                        @endphp
                                        @if($tiles->isNotEmpty())
                                            <h6 class="text-muted text-uppercase mt-3 mb-2 small" style="letter-spacing:0.04em;">
                                                {{ \App\Models\LinkType::CATEGORY_LABELS[$catKey] ?? ucfirst($catKey) }}
                                            </h6>
                                            <div class="d-flex flex-row flex-wrap mb-2">
                                                @foreach ($tiles as $lt)
                                                    @php
                                                        if (block_text_translation_check($lt['title'])) {
                                                            $title = bt($lt['title']);
                                                        } else {
                                                            $title = __('messages.block.title.' . $lt['typename']);
                                                        }
                                                        $description = bt($lt['description']) ?? __('messages.block.description.' . $lt['typename']);
                                                    @endphp
                                                    <a href="#" data-typeid="{{ $lt['typename'] }}" data-typename="{{ $title }}" class="hvr-grow m-2 w-100 d-block doSelectLinkType">
                                                        <div class="rounded mb-3 shadow-lg">
                                                            <div class="row g-0">
                                                                <div class="col-auto bg-light d-flex align-items-center justify-content-center p-3">
                                                                    <i class="{{ $lt['icon'] }} text-primary h1 mb-0"></i>
                                                                </div>
                                                                <div class="col">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title text-dark mb-0">{{ $title }}</h5>
                                                                        <p class="card-text text-muted">{{ $description }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        {{-- Modal: hosts the chosen block's form. The whole
                             <form> wraps body+footer so the Save button
                             actually submits. data-bs-dismiss uses BS5 syntax;
                             page already loads BS5 via the sidebar layout. --}}
                        <div class="modal fade" id="addBlockModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('addLink') }}" method="post" id="my-form">
                                        @method('POST')
                                        @csrf
                                        <input type='hidden' name='linkid' value="0" />
                                        <input type='hidden' name='typename' value='' />
                                        @if(!empty($embed))<input type="hidden" name="embed" value="1">@endif

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                {{__('messages.Add')}}: <span id="selectedBlockName" class="text-primary">&mdash;</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="link_params"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-dismiss="modal">{{__('messages.Cancel')}}</button>
                                            <button type="button" class="btn btn-soft-primary" onclick="submitFormWithParam('add_more')">{{__('messages.Save and Add More')}}</button>
                                            <button type="submit" class="btn btn-primary">{{__('messages.Save')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- ====================================================
                             EDIT MODE: three labelled sections in one form —
                             Content (type-specific fields, AJAX-loaded),
                             Appearance (icon + per-block CSS overrides, was
                             previously the standalone /studio/button-editor
                             page), Settings (page-level block toggles).
                             One Save button covers all three.
                             ==================================================== --}}
                        @php
                            // Load the existing per-block customization values
                            // so the Appearance section can pre-fill on edit.
                            // - custom_css + custom_icon are real columns
                            // - appearance state (preset, primary/text/secondary
                            //   color, shape, hover) lives in type_params JSON
                            //   so re-edit can populate controls deterministically
                            //   without parsing the generated CSS string.
                            $existingLink   = \App\Models\Link::where('id', $LinkID)->first();
                            $existingCss    = $existingLink->custom_css ?? '';
                            $existingIcon   = $existingLink->custom_icon ?? '';

                            $existingTP = [];
                            if (!empty($existingLink->type_params)) {
                                $decoded = json_decode($existingLink->type_params, true);
                                if (is_array($decoded)) {
                                    $existingTP = $decoded;
                                }
                            }

                            // Per-block CSS only affects button-family blocks at
                            // render (elements/buttons.blade.php); custom_html
                            // blocks (contact form, Stripe, text, …) never read
                            // it, so don't show dead controls for them.
                            $blockSupportsAppearance = empty($existingTP['custom_html']);

                            // Theme baseline (Phase 5, THEME-APPEARANCE-PLAN.md):
                            // a block with no styling of its own hydrates the
                            // controls from the page's EFFECTIVE button look —
                            // theme manifest + the user's page-wide overrides —
                            // mapped onto this editor's preset/shape vocabulary.
                            // The JS diffs against this baseline so a pristine
                            // block stays pristine (custom_css only saves when
                            // the user actually diverges from the theme).
                            $mmEffective = \App\Http\Controllers\AppearanceController::effectiveForUser(Auth::user());
                            $mmBaseline = [
                                'preset'  => ['filled' => 'filled', 'outline' => 'outlined', 'soft' => 'soft'][$mmEffective['buttons']['style']] ?? 'filled',
                                'primary' => $mmEffective['colors']['primary'],
                                'text'    => $mmEffective['colors']['button_text'],
                                'shape'   => ['pill' => 999, 'rounded' => 16, 'square' => 0][$mmEffective['buttons']['shape']] ?? 16,
                            ];

                            // Preview stage wears the page's effective background
                            // so the sample button is judged in context.
                            $mmBg = $mmEffective['background'];
                            $mmStageBg = match ($mmBg['type'] ?? 'solid') {
                                'gradient' => "background: linear-gradient({$mmBg['gradient_direction']}, {$mmBg['gradient_start']}, {$mmBg['gradient_end']});",
                                'image'    => !empty($mmBg['image_url'])
                                    ? "background: url('{$mmBg['image_url']}') center / cover no-repeat;"
                                    : "background: {$mmEffective['colors']['background']};",
                                default    => "background: {$mmBg['solid']};",
                            };

                            $apPreset    = $existingTP['appearance_preset']    ?? $mmBaseline['preset'];
                            $apPrimary   = $existingTP['appearance_primary']   ?? $mmBaseline['primary'];
                            $apText      = $existingTP['appearance_text']      ?? $mmBaseline['text'];
                            $apSecondary = $existingTP['appearance_secondary'] ?? '#764ba2';
                            $apShape     = $existingTP['appearance_shape']     ?? $mmBaseline['shape'];
                            $apHover     = $existingTP['appearance_hover']     ?? 'lift';
                            $apAdvanced  = $existingTP['appearance_advanced']  ?? '';
                        @endphp

                        <a href="{{ url('studio/links') }}" class="mm-edit-back"><i class="bi bi-arrow-left"></i> Back to Blocks</a>
                        <section class='text-gray-400'>
                            <h3 class="mb-4 card-header"><i class="bi bi-journal-plus"></i> {{__('messages.Edit')}} {{__('messages.Block')}}</h3>
                            <div class='card-body'>
                                <form action="{{ route('addLink') }}" method="post" id="my-form">
                                    @method('POST')
                                    @csrf
                                    <input type='hidden' name='linkid' value="{{ $LinkID }}" />
                                    <input type='hidden' name='typename' value='{{ $typename }}' />
                                    @if(!empty($embed))<input type="hidden" name="embed" value="1">@endif

                                    {{-- ===== Content section ===== --}}
                                    <fieldset class="mm-edit-section">
                                        <legend><i class="bi bi-pencil"></i> Content</legend>
                                        <div id='link_params' class='col-lg-12'></div>
                                    </fieldset>

                                    {{-- ===== Appearance section =====
                                         Visual controls — no CSS knowledge required.

                                         Mental model:
                                         - Presets define the TREATMENT (solid fill,
                                           outline, gradient, glass, neon, etc.) —
                                           NOT the colors. Switching presets never
                                           wipes the user's colors.
                                         - User always picks Primary, Text colors.
                                         - Secondary color picker appears only when
                                           the Gradient preset is active.
                                         - Shape chips set border-radius independently.
                                         - Hover effect dropdown stored for later.
                                         - Icon picker stores into custom_icon.

                                         Storage:
                                         - `custom_css` column ← generated CSS string
                                         - `custom_icon` column ← FA icon class
                                         - `type_params` JSON ← raw state (preset +
                                           each color + shape + hover) so re-edit can
                                           rehydrate controls without parsing CSS. --}}
                                    @if($blockSupportsAppearance)
                                    <fieldset class="mm-edit-section mm-appearance" id="appearance">
                                        <legend><i class="bi bi-palette"></i> Appearance</legend>
                                        <p class="text-muted small mb-3">
                                            Your theme styles this block automatically &mdash; tweak the
                                            style, colors, shape, or icon only if you want this one block
                                            to stand out. Changes preview live above.
                                        </p>

                                        {{-- Live preview button on the page's own background.
                                             The icon lives inside a wrapper span so the JS can
                                             swap its innerHTML when the user picks a new icon —
                                             that triggers FontAwesome's MutationObserver and the
                                             new <i> gets converted to its <svg> glyph. --}}
                                        <div class="mm-preview-wrap">
                                            <span class="mm-preview-label small text-muted">Preview</span>
                                            <div class="mm-preview-stage" style="{{ $mmStageBg }}">
                                                <button type="button" id="mmPreviewBtn" class="mm-preview-btn">
                                                    <span id="mmPreviewIconWrap"></span>
                                                    <span id="mmPreviewLabel">{{ $existingLink->title ?? 'Sample button' }}</span>
                                                </button>
                                            </div>
                                            <button type="button" id="mmResetTheme" class="btn btn-sm btn-outline-secondary mt-2">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset to theme
                                            </button>
                                        </div>

                                        {{-- ===== Style presets =====
                                             Each preset is a *treatment* — Filled,
                                             Outlined, Gradient, etc. — that decides
                                             HOW the user's primary/text colors are
                                             used. Clicking a preset never touches
                                             the colors themselves; it only flips the
                                             treatment switch. --}}
                                        <div class="mm-control-group">
                                            <label class="mm-control-label">Style</label>
                                            <div class="mm-preset-grid" id="mmPresetGrid">
                                                <button type="button" class="mm-preset" data-preset="filled">
                                                    <span class="mm-preset-swatch mm-swatch-filled">Aa</span>
                                                    <span class="mm-preset-name">Filled</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="outlined">
                                                    <span class="mm-preset-swatch mm-swatch-outlined">Aa</span>
                                                    <span class="mm-preset-name">Outlined</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="gradient">
                                                    <span class="mm-preset-swatch mm-swatch-gradient">Aa</span>
                                                    <span class="mm-preset-name">Gradient</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="soft">
                                                    <span class="mm-preset-swatch mm-swatch-soft">Aa</span>
                                                    <span class="mm-preset-name">Soft</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="glass">
                                                    <span class="mm-preset-swatch mm-swatch-glass">Aa</span>
                                                    <span class="mm-preset-name">Glass</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="neon">
                                                    <span class="mm-preset-swatch mm-swatch-neon">Aa</span>
                                                    <span class="mm-preset-name">Neon</span>
                                                </button>
                                                <button type="button" class="mm-preset" data-preset="ghost">
                                                    <span class="mm-preset-swatch mm-swatch-ghost">Aa</span>
                                                    <span class="mm-preset-name">Ghost</span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- ===== Colors =====
                                             Primary + Text are always shown. Secondary
                                             ONLY shows when the Gradient preset is
                                             active (CSS toggles it via .mm-appearance
                                             attribute below). --}}
                                        <div class="row g-3 mt-2">
                                            <div class="col-sm-4">
                                                <label class="mm-control-label" for="mmPrimaryColor">Button color</label>
                                                <input type="color" id="mmPrimaryColor" value="{{ $apPrimary }}" class="form-control form-control-color" style="width: 100%;">
                                            </div>
                                            <div class="col-sm-4">
                                                <label class="mm-control-label" for="mmTextColor">Text color</label>
                                                <input type="color" id="mmTextColor" value="{{ $apText }}" class="form-control form-control-color" style="width: 100%;">
                                            </div>
                                            <div class="col-sm-4 mm-secondary-wrap">
                                                <label class="mm-control-label" for="mmSecondaryColor">Gradient end</label>
                                                <input type="color" id="mmSecondaryColor" value="{{ $apSecondary }}" class="form-control form-control-color" style="width: 100%;">
                                            </div>
                                        </div>

                                        {{-- ===== Shape ===== --}}
                                        <div class="mm-control-group">
                                            <label class="mm-control-label">Shape</label>
                                            <div class="mm-chip-row" id="mmShapeChips">
                                                <button type="button" class="mm-chip @if((int)$apShape === 0) active @endif" data-shape="0">Square</button>
                                                <button type="button" class="mm-chip @if((int)$apShape === 8) active @endif" data-shape="8">Slight</button>
                                                <button type="button" class="mm-chip @if((int)$apShape === 16) active @endif" data-shape="16">Rounded</button>
                                                <button type="button" class="mm-chip @if((int)$apShape >= 999) active @endif" data-shape="999">Pill</button>
                                            </div>
                                        </div>

                                        {{-- ===== Hover effect =====
                                             Stored in state but not yet applied to
                                             the public-page button (requires a
                                             per-block <style> block — coming in a
                                             follow-up; tracked in UI-PASS-PLAN.md). --}}
                                        <div class="mm-control-group">
                                            <label class="mm-control-label" for="mmHover">Hover effect</label>
                                            <select id="mmHover" class="form-select" style="max-width: 260px;">
                                                <option value="none"       @if($apHover === 'none')       selected @endif>None</option>
                                                <option value="lift"       @if($apHover === 'lift')       selected @endif>Lift up</option>
                                                <option value="glow"       @if($apHover === 'glow')       selected @endif>Soft glow</option>
                                                <option value="scale"      @if($apHover === 'scale')      selected @endif>Scale up</option>
                                                <option value="colorshift" @if($apHover === 'colorshift') selected @endif>Color shift</option>
                                            </select>
                                        </div>

                                        {{-- ===== Icon ===== --}}
                                        <div class="mm-control-group">
                                            <label class="mm-control-label">Icon</label>
                                            {{-- Generic utility icons only. For branded socials
                                                 use the Social Icons page (top row) or the
                                                 Predefined Site block (full button) — both auto-
                                                 pick the brand glyph based on which brand you
                                                 pick. Mixing brand icons into the Custom Link
                                                 picker would be redundant with those flows. --}}
                                            <div class="mm-icon-grid" id="mmIconGrid">
                                                <button type="button" class="mm-icon-tile" data-icon=""                              title="None"><i class="bi bi-slash-circle"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-link"              title="Link"><i class="fa-solid fa-link"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-envelope"          title="Email"><i class="fa-solid fa-envelope"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-phone"             title="Phone"><i class="fa-solid fa-phone"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-location-dot"      title="Location"><i class="fa-solid fa-location-dot"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-calendar"          title="Calendar"><i class="fa-solid fa-calendar"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-cart-shopping"     title="Shop"><i class="fa-solid fa-cart-shopping"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-heart"             title="Heart"><i class="fa-solid fa-heart"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-star"              title="Star"><i class="fa-solid fa-star"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-music"             title="Music"><i class="fa-solid fa-music"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-camera"            title="Camera"><i class="fa-solid fa-camera"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-briefcase"         title="Briefcase"><i class="fa-solid fa-briefcase"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-newspaper"         title="News / blog"><i class="fa-solid fa-newspaper"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-circle-info"       title="Info"><i class="fa-solid fa-circle-info"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-gift"              title="Gift / offer"><i class="fa-solid fa-gift"></i></button>
                                                <button type="button" class="mm-icon-tile" data-icon="fa-solid fa-file-lines"        title="Document"><i class="fa-solid fa-file-lines"></i></button>
                                            </div>
                                            <details class="mt-2">
                                                <summary class="small text-muted" style="cursor: pointer;">Need a different icon?</summary>
                                                <div class="mt-2">
                                                    <input type="text"
                                                           id="mmIconCustom"
                                                           value="{{ $existingIcon }}"
                                                           class="form-control"
                                                           placeholder="e.g. fa-solid fa-rocket"
                                                           style="max-width: 320px;">
                                                    <small class="text-muted">
                                                        Any <a href="https://fontawesome.com/search?m=free" target="_blank" rel="noopener">Font Awesome</a> class.
                                                        For branded socials, use the
                                                        <a href="{{ url('/studio/social-icons') }}">Social Icons</a>
                                                        page or a Predefined Site block instead.
                                                    </small>
                                                </div>
                                            </details>
                                        </div>

                                        {{-- ===== Advanced (hidden by default) ===== --}}
                                        <details class="mm-advanced mt-3">
                                            <summary class="small text-muted" style="cursor: pointer;">
                                                Advanced &mdash; hand-edit the generated styles
                                            </summary>
                                            <div class="mt-2">
                                                <p class="small text-muted">
                                                    For users comfortable writing CSS. Anything here is appended after
                                                    the visual controls' output, so it can override individual properties.
                                                </p>
                                                <textarea id="mmAdvancedCss"
                                                          rows="6"
                                                          class="form-control mm-css-textarea"
                                                          placeholder="e.g. letter-spacing: 0.05em; text-transform: uppercase;">{{ $apAdvanced }}</textarea>
                                            </div>
                                        </details>

                                        {{-- Hidden form fields the server receives.
                                             - custom_css / custom_icon are real columns
                                             - appearance_* keys auto-route to type_params
                                               JSON via saveLink's array_diff_key (since
                                               they're not columns), giving us round-trip
                                               state without any controller changes. --}}
                                        <input type="hidden" name="custom_css"           id="custom_css"           value="{{ $existingCss }}">
                                        <input type="hidden" name="custom_icon"          id="custom_icon"          value="{{ $existingIcon }}">
                                        <input type="hidden" name="appearance_preset"    id="appPreset"    value="{{ $apPreset }}">
                                        <input type="hidden" name="appearance_primary"   id="appPrimary"   value="{{ $apPrimary }}">
                                        <input type="hidden" name="appearance_text"      id="appText"      value="{{ $apText }}">
                                        <input type="hidden" name="appearance_secondary" id="appSecondary" value="{{ $apSecondary }}">
                                        <input type="hidden" name="appearance_shape"     id="appShape"     value="{{ $apShape }}">
                                        <input type="hidden" name="appearance_hover"     id="appHover"     value="{{ $apHover }}">
                                        <input type="hidden" name="appearance_advanced"  id="appAdvanced"  value="{{ $apAdvanced }}">

                                        <script>window.MM_BLOCK_BASELINE = @json($mmBaseline);</script>
                                    </fieldset>
                                    @else
                                    {{-- custom_html blocks (contact form, payments, text, …)
                                         never consume per-block CSS — the controls used to
                                         show here anyway and silently did nothing. Explain
                                         instead of leaving a hole where a section was. --}}
                                    <fieldset class="mm-edit-section">
                                        <legend><i class="bi bi-palette"></i> Appearance</legend>
                                        <p class="text-muted small mb-0">
                                            This block is styled by your theme automatically &mdash; it
                                            doesn't have per-block style controls. To change your page's
                                            overall look, use the <strong>Themes</strong> and
                                            <strong>Appearance</strong> tabs.
                                        </p>
                                    </fieldset>
                                    @endif

                                    {{-- ===== Settings section ===== --}}
                                    <fieldset class="mm-edit-section">
                                        <legend><i class="bi bi-toggles"></i> Settings</legend>
                                        <p class="text-muted small mb-2">
                                            Page-level toggles for this block. Block-specific options
                                            (like the contact form's collapsed toggle) live inside the
                                            Content section above.
                                        </p>
                                        {{-- Placeholder — when we have page-level block settings
                                             (e.g. enabled/disabled, scheduled visibility), they'll
                                             land here. Currently the only per-block setting is the
                                             "Start collapsed" toggle which lives inside each
                                             custom_html block's own form. --}}
                                        <p class="text-muted small fst-italic mb-0">
                                            More settings coming soon.
                                        </p>
                                    </fieldset>

                                    <div class="d-flex align-items-center pt-2">
                                        <a class="btn btn-danger me-3" href="{{ url('studio/links') }}">{{__('messages.Cancel')}}</a>
                                        <button type="submit" class="btn btn-primary me-3">{{__('messages.Save')}}</button>
                                        <button type="button" class="btn btn-soft-primary me-3" onclick="submitFormWithParam('add_more')">{{__('messages.Save and Add More')}}</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    @endif

                   </div>
               </div>
            </div>
         </div>
        </div>
      </div>
    </div>

<script>
function submitFormWithParam(paramValue) {
    var form = document.getElementById("my-form");
    var paramField = document.createElement("input");
    paramField.setAttribute("type", "hidden");
    paramField.setAttribute("name", "param");
    paramField.setAttribute("value", paramValue);
    form.appendChild(paramField);
    form.submit();
}
</script>

@endsection

@push("sidebar-scripts")
<script>
/* ============================================================
   Appearance section — visual controls
   ============================================================
   The operator picks a preset and/or tweaks individual controls.
   On every change, we recompute a `state` object and render it
   into:
     1. The live preview button at the top of the section
     2. The hidden #custom_css input that gets POSTed on save

   No CSS string ever appears in the primary UI — the textarea
   inside the "Advanced" expander is appended at the end so
   power users can still hand-tweak. saveLink in the controller
   picks up custom_css + custom_icon via array_intersect_key
   against the `links` table columns.
   ============================================================ */
(function () {
    var $previewBtn = document.getElementById('mmPreviewBtn');
    if (!$previewBtn) return;  /* Appearance section only renders in edit mode */

    /* --------- DOM refs --------- */
    var $appearance      = document.getElementById('appearance');
    var $previewIconWrap = document.getElementById('mmPreviewIconWrap');
    var $primaryColor    = document.getElementById('mmPrimaryColor');
    var $textColor       = document.getElementById('mmTextColor');
    var $secondaryColor  = document.getElementById('mmSecondaryColor');
    var $shapeChips      = document.getElementById('mmShapeChips');
    var $hover           = document.getElementById('mmHover');
    var $presetGrid      = document.getElementById('mmPresetGrid');
    var $iconGrid        = document.getElementById('mmIconGrid');
    var $iconCustom      = document.getElementById('mmIconCustom');
    var $advancedCss     = document.getElementById('mmAdvancedCss');

    /* --------- Hidden form fields --------- */
    var $cssInput        = document.getElementById('custom_css');
    var $iconInput       = document.getElementById('custom_icon');
    var $hPreset         = document.getElementById('appPreset');
    var $hPrimary        = document.getElementById('appPrimary');
    var $hText           = document.getElementById('appText');
    var $hSecondary      = document.getElementById('appSecondary');
    var $hShape          = document.getElementById('appShape');
    var $hHover          = document.getElementById('appHover');
    var $hAdvanced       = document.getElementById('appAdvanced');

    /* --------- State model ---------
       Independent variables — presets change ONLY `preset`.
       Colors and shape are owned by the user, never overwritten
       when switching presets. */
    var state = {
        preset:    $hPreset.value    || 'filled',
        primary:   $hPrimary.value   || '#3b82f6',
        text:      $hText.value      || '#ffffff',
        secondary: $hSecondary.value || '#764ba2',
        shape:     parseInt($hShape.value, 10) || 16,
        hover:     $hHover.value     || 'lift',
        icon:      $iconInput.value  || '',
        advanced:  $hAdvanced.value  || ''
    };

    /* --------- CSS generation ---------
       Switches on the preset's treatment. Each treatment knows how
       to use the user's three colors and the shape. The output is
       a deterministic style string applied inline. */
    function generateCss() {
        var p = state.primary;
        var t = state.text;
        var s = state.secondary;
        var r = state.shape + 'px';
        var css = [];

        /* Every emitted property gets !important so our customization
           wins against theme CSS that uses !important to lock its own
           button colors (PolySleek does this for bg + text). Without
           the marker, theme styles override our inline ones and the
           user's choices visually disappear. */
        function decl(prop, value) {
            css.push(prop + ': ' + value + ' !important');
        }

        switch (state.preset) {
            case 'filled':
                decl('background', p);
                decl('background-image', 'none');
                decl('color', t);
                decl('border', 'none');
                break;

            case 'outlined':
                decl('background', 'transparent');
                decl('background-image', 'none');
                decl('color', p);
                decl('border', '2px solid ' + p);
                break;

            case 'gradient':
                decl('background-color', p);
                decl('background-image', 'linear-gradient(135deg, ' + p + ', ' + s + ')');
                decl('color', t);
                decl('border', 'none');
                break;

            case 'soft':
                /* Tint the primary color: low-saturation light bg,
                   text uses the primary at full strength. */
                decl('background', withAlpha(p, 0.18));
                decl('background-image', 'none');
                decl('color', p);
                decl('border', 'none');
                break;

            case 'glass':
                decl('background', withAlpha(p, 0.22));
                decl('background-image', 'none');
                decl('backdrop-filter', 'blur(10px)');
                decl('-webkit-backdrop-filter', 'blur(10px)');
                decl('color', t);
                decl('border', '1px solid ' + withAlpha('#ffffff', 0.45));
                break;

            case 'neon':
                /* The dark plate + glowing text effect. Primary
                   color drives the glow + the text color. Text-color
                   picker is ignored in this preset (would just dim
                   the glow). */
                decl('background', '#0a0a0a');
                decl('background-image', 'none');
                decl('color', p);
                decl('border', '1px solid ' + withAlpha(p, 0.5));
                decl('box-shadow', '0 0 18px ' + withAlpha(p, 0.65));
                break;

            case 'ghost':
                decl('background', 'transparent');
                decl('background-image', 'none');
                decl('color', p);
                decl('border', 'none');
                break;

            default:
                /* Defensive: fall back to filled if state.preset
                   somehow has an unknown value. */
                decl('background', p);
                decl('color', t);
                decl('border', 'none');
        }

        decl('border-radius', r);

        var generated = css.join('; ') + ';';
        var advanced  = (state.advanced || '').trim();
        return advanced ? generated + ' ' + advanced : generated;
    }

    /* Convert "#RRGGBB" into "rgba(r, g, b, a)" for blendable
       backgrounds. If parsing fails (e.g., already an rgba), return
       the input unchanged. */
    function withAlpha(hex, alpha) {
        var m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
        if (!m) return hex;
        var n = parseInt(m[1], 16);
        var r = (n >> 16) & 255;
        var g = (n >> 8)  & 255;
        var b = n & 255;
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    /* --------- Theme baseline (Phase 5) ---------
       The page's effective button look (theme manifest + page-wide
       overrides), injected server-side. When the current state matches
       it, the block is "just following the theme" and we save an EMPTY
       custom_css — the server then also drops the appearance_* state,
       so the block keeps tracking the theme (including future theme
       switches). Diverge and only then does styling get frozen in. */
    var baseline = window.MM_BLOCK_BASELINE || null;

    function matchesBaseline() {
        if (!baseline) return false;
        if ((state.advanced || '').trim() !== '') return false;
        if (state.preset !== baseline.preset) return false;
        if (state.shape !== baseline.shape) return false;
        if (String(state.primary).toLowerCase() !== String(baseline.primary).toLowerCase()) return false;
        // Text color only affects the generated CSS in presets that
        // use it; ignore it elsewhere so an inert change doesn't
        // needlessly freeze the block.
        var usesText = (state.preset === 'filled' || state.preset === 'gradient' || state.preset === 'glass');
        if (usesText && String(state.text).toLowerCase() !== String(baseline.text).toLowerCase()) return false;
        return true;
    }

    /* --------- Render passes --------- */
    function syncHiddenInputs() {
        $cssInput.value   = matchesBaseline() ? '' : generateCss();
        $iconInput.value  = state.icon || '';
        $hPreset.value    = state.preset;
        $hPrimary.value   = state.primary;
        $hText.value      = state.text;
        $hSecondary.value = state.secondary;
        $hShape.value     = String(state.shape);
        $hHover.value     = state.hover;
        $hAdvanced.value  = state.advanced;
    }

    function applyToPreview() {
        var css = generateCss();
        var presentation = ' padding: 12px 24px; font-size: 1rem; font-weight: 500; min-width: 200px; display: inline-flex; align-items: center; gap: 8px; justify-content: center; cursor: default; transition: all 0.18s ease;';
        $previewBtn.setAttribute('style', css + presentation);
    }

    function syncAll() {
        applyToPreview();
        syncHiddenInputs();
    }

    /* --------- Treatment toggling ---------
       Sets data-treatment on the section root so CSS can hide/show
       the secondary color picker (only relevant for the gradient
       preset). Also lights up the active preset card. */
    function reflectPreset() {
        $appearance.setAttribute('data-treatment', state.preset);
        Array.from($presetGrid.children).forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.preset === state.preset);
        });
    }

    function reflectShape() {
        Array.from($shapeChips.children).forEach(function (chip) {
            chip.classList.toggle('active', parseInt(chip.dataset.shape, 10) === state.shape);
        });
    }

    /* --------- Wire controls --------- */

    /* Preset clicks change ONLY the preset/treatment. Colors,
       shape, icon, hover are preserved. */
    Array.from($presetGrid.children).forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.preset = btn.dataset.preset;
            reflectPreset();
            syncAll();
        });
    });

    /* Color pickers — listen for both `input` and `change` to
       handle browser inconsistencies. */
    function onPrimary() { state.primary   = $primaryColor.value;   syncAll(); }
    function onText()    { state.text      = $textColor.value;      syncAll(); }
    function onSecondary(){ state.secondary= $secondaryColor.value; syncAll(); }
    $primaryColor.addEventListener('input',  onPrimary);
    $primaryColor.addEventListener('change', onPrimary);
    $textColor.addEventListener('input',     onText);
    $textColor.addEventListener('change',    onText);
    $secondaryColor.addEventListener('input',  onSecondary);
    $secondaryColor.addEventListener('change', onSecondary);

    /* Shape chips */
    Array.from($shapeChips.children).forEach(function (chip) {
        chip.addEventListener('click', function () {
            state.shape = parseInt(chip.dataset.shape, 10);
            reflectShape();
            syncAll();
        });
    });

    /* Hover effect (stored; render-time support coming in follow-up) */
    $hover.addEventListener('change', function () {
        state.hover = this.value;
        syncHiddenInputs();
    });

    /* Icon picker — visual grid + custom text input write to the
       same state. Last write wins. */
    function highlightIconTile(iconClass) {
        Array.from($iconGrid.children).forEach(function (tile) {
            tile.classList.toggle('active', tile.dataset.icon === iconClass);
        });
    }
    Array.from($iconGrid.children).forEach(function (tile) {
        tile.addEventListener('click', function () {
            state.icon = tile.dataset.icon || '';
            $iconCustom.value = state.icon;
            highlightIconTile(state.icon);
            updatePreviewIcon();
            syncHiddenInputs();
        });
    });
    $iconCustom.addEventListener('input', function () {
        state.icon = this.value.trim();
        highlightIconTile(state.icon);
        updatePreviewIcon();
        syncHiddenInputs();
    });
    /* Swap icon HTML via innerHTML so FontAwesome's MutationObserver
       picks up the new <i> tag and converts it to its SVG glyph.
       Setting className on an already-converted <svg> wouldn't
       trigger re-conversion. */
    function updatePreviewIcon() {
        if (!$previewIconWrap) return;
        $previewIconWrap.innerHTML = state.icon ? '<i class="' + state.icon + '"></i>' : '';
    }

    /* Advanced CSS textarea — appended to generated output. */
    $advancedCss.addEventListener('input', function () {
        state.advanced = $advancedCss.value;
        syncAll();
    });

    /* NOTE: no submit-time flush. Every control handler above syncs
       the hidden inputs as it fires, and an unconditional flush here
       was the bug that froze pristine blocks: saving ANY edit (even a
       title fix) generated custom_css from the hydrated defaults and
       the block stopped following the theme. Untouched controls now
       leave the server-rendered hidden values exactly as they were. */

    /* --------- Reset to theme (Phase 5) ---------
       Puts the styling state back to the theme baseline; the
       matchesBaseline() check then saves an empty custom_css and the
       block follows the theme again. Icon is content, not styling —
       it survives the reset. */
    var $resetTheme = document.getElementById('mmResetTheme');
    if ($resetTheme) {
        if (!baseline) {
            $resetTheme.style.display = 'none';
        } else {
            $resetTheme.addEventListener('click', function () {
                state.preset   = baseline.preset;
                state.primary  = baseline.primary;
                state.text     = baseline.text;
                state.shape    = baseline.shape;
                state.advanced = '';
                $primaryColor.value = baseline.primary;
                $textColor.value    = baseline.text;
                $advancedCss.value  = '';
                reflectPreset();
                reflectShape();
                syncAll();
            });
        }
    }

    /* --------- Initialize from server-rendered state --------- */
    reflectPreset();
    reflectShape();
    highlightIconTile(state.icon);
    updatePreviewIcon();
    applyToPreview();
    /* Don't call syncHiddenInputs here — keeps form pristine until
       user actually changes something. Their submit listener does
       the final flush regardless. */
})();
</script>

<script>
$(function() {
    var linkId      = $("input[name='linkid']").val();
    var initialType = $("input[name='typename']").val();

    // Edit mode: load that block's fields immediately into the on-page form.
    if (linkId && linkId !== "0" && initialType) {
        LoadLinkTypeParams(initialType, linkId);
        return;
    }

    // Add mode: tile-click loads that block's fields into the modal,
    // then opens it. LinkStack ships Bootstrap 4.3.1 (assets/js/bootstrap.min.js),
    // so the modal API is jQuery's `.modal('show')` — not BS5's
    // bootstrap.Modal.getOrCreateInstance.
    $('.doSelectLinkType').on('click', function(e) {
        e.preventDefault();
        var typeId   = $(this).data('typeid');
        var typeName = $(this).data('typename');
        $("input[name='typename']").val(typeId);
        $("#selectedBlockName").text(typeName);
        LoadLinkTypeParams(typeId, "0");
        $('#addBlockModal').modal('show');
    });

    function LoadLinkTypeParams(typeId, currentLinkId) {
        var baseURL = <?php echo "\"" . url('') . "\""; ?>;
        $("#link_params").html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>')
                         .load(baseURL + '/studio/linkparamform_part/' + typeId + '/' + currentLinkId);
        setTimeout(function() { document.dispatchEvent(new Event('contentLoaded')); }, 300);
    }
});
</script>
@endpush
