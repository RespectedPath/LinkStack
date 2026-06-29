{{--
    Per-user Appearance overrides. Loaded after theme CSS so its
    rules cascade and win. Pushed from linkstack.blade.php into the
    linkstack-head-end stack. Skipped entirely when the user has no
    saved customization so default theme rendering is untouched.

    Storage: users.theme_customization (JSON). Editor at /studio/appearance.
    AppearanceController::loadForUser() hydrates the JSON against
    defaults so every key is always present.
--}}

@php
    $ac = App\Http\Controllers\AppearanceController::loadForUser($userinfo ?? null);
    $isSet = App\Http\Controllers\AppearanceController::isConfigured($userinfo ?? null);
@endphp

@if($isSet)
    {{-- Google Fonts: only load the selected family, nothing else. --}}
    @if(!empty($ac['typography']['font']))
        @php
            $googleFont = str_replace(' ', '+', $ac['typography']['font']);
        @endphp
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ $googleFont }}:wght@400;500;600;700&display=swap">
    @endif

    @php
        // Build the background declaration with all longhand props
        // explicit. Using shorthand would leak stale longhand values
        // from the preview override stacked on top.
        $bg = $ac['background'];
        switch ($bg['type']) {
            case 'gradient':
                $bgCss = "background-image: linear-gradient({$bg['gradient_direction']}, {$bg['gradient_start']}, {$bg['gradient_end']}) !important;"
                       . "background-color: transparent !important;"
                       . "background-size: auto !important;"
                       . "background-position: 0% 0% !important;"
                       . "background-repeat: no-repeat !important;"
                       . "background-attachment: fixed !important;";
                break;
            case 'image':
                $url = $bg['image_url'] ?? '';
                // Accept http(s) URLs (legacy/external) OR /assets/...
                // local paths (new uploaded bg).
                $isOk = !empty($url) && (preg_match('/^https?:\/\//i', $url) || strpos($url, '/assets/') === 0);
                if ($isOk) {
                    $bgCss = "background-image: url('" . addslashes($url) . "') !important;"
                           . "background-size: cover !important;"
                           . "background-position: center !important;"
                           . "background-repeat: no-repeat !important;"
                           . "background-attachment: fixed !important;"
                           . "background-color: {$ac['colors']['background']} !important;";
                } else {
                    $bgCss = "background-image: none !important;"
                           . "background-color: {$ac['colors']['background']} !important;";
                }
                break;
            case 'solid':
            default:
                $bgCss = "background-image: none !important;"
                       . "background-color: {$bg['solid']} !important;"
                       . "background-size: auto !important;"
                       . "background-position: 0% 0% !important;"
                       . "background-repeat: repeat !important;"
                       . "background-attachment: scroll !important;";
        }

        // Button style maps to a CSS class suffix on body that our
        // override rules target. Same for shape.
        $shape = $ac['buttons']['shape'];
        $style = $ac['buttons']['style'];
        $avatarShape = $ac['avatar']['shape'];

        $shapeRadius = ['pill' => '999px', 'rounded' => '10px', 'square' => '0'][$shape] ?? '10px';
        $avatarRadius = $avatarShape === 'rounded_square' ? '14px' : '50%';
        $avatarHidden = $avatarShape === 'off';

        $primary      = $ac['colors']['primary'];
        $buttonText   = $ac['colors']['button_text'];
        $textColor    = $ac['colors']['text'];

        // rgba for the "soft" fill — approx 15% opacity of the primary.
        $r = hexdec(substr($primary, 1, 2));
        $g = hexdec(substr($primary, 3, 2));
        $b = hexdec(substr($primary, 5, 2));
        $primarySoft = "rgba($r, $g, $b, 0.15)";

        // Fill-vs-outline-vs-soft CSS. Written as separate blocks so
        // the style cascade is predictable.
        if ($style === 'filled') {
            $buttonStyleCss = "background-color: {$primary} !important; background-image: none !important; color: {$buttonText} !important; border: 2px solid {$primary} !important;";
        } elseif ($style === 'outline') {
            $buttonStyleCss = "background-color: transparent !important; background-image: none !important; color: {$primary} !important; border: 2px solid {$primary} !important;";
        } else { // soft
            $buttonStyleCss = "background-color: {$primarySoft} !important; background-image: none !important; color: {$primary} !important; border: 2px solid transparent !important;";
        }
    @endphp

    <style id="user-appearance-override">
        :root {
            --user-primary:     {{ $primary }};
            --user-bg:          {{ $ac['colors']['background'] }};
            --user-text:        {{ $textColor }};
            --user-button-text: {{ $buttonText }};
        }

        /* Body background + text color + font family */
        body {
            {!! $bgCss !!}
            color: {{ $textColor }} !important;
            @if(!empty($ac['typography']['font']))
            font-family: '{{ $ac['typography']['font'] }}', -apple-system, BlinkMacSystemFont, sans-serif !important;
            @endif
        }

        /* Heading + description inherit the text color so they read
           correctly on whatever background was picked. */
        .header-name, .header-description, h1, h2, h3, h4, h5, h6, p {
            color: {{ $textColor }} !important;
        }

        /* Links / buttons — shape + style together. Targets .button
           class which every link-block button receives, plus common
           theme button selectors. */
        .button, .button-custom, .button-custom_website, a.button, .button-default {
            {!! $buttonStyleCss !!}
            border-radius: {{ $shapeRadius }} !important;
        }
        .button:hover, .button-custom:hover, .button-custom_website:hover, a.button:hover, .button-default:hover {
            filter: brightness(0.92);
        }

        /* Avatar — #avatar is present on the user's uploaded image,
           the instance avatar fallback, and the logo fallback; each
           branch in resources/views/linkstack/elements/avatar.blade.php
           emits the same id, so hiding it here always works. */
        @if($avatarHidden)
        /* Hide the avatar visually while keeping its space reserved so
           links and blocks below don't jump up when it's off. */
        #avatar {
            visibility: hidden !important;
        }
        @else
        #avatar, .rounded-avatar, img.rounded-avatar {
            border-radius: {{ $avatarRadius }} !important;
        }
        @endif

        {{-- ===== Social icons (Pass 4) ===== --}}
        @php
            $si = $ac['social_icons'];

            // Size → glyph font-size + padding around it
            $sizes = ['small' => 22, 'medium' => 30, 'large' => 38, 'xl' => 46];
            $siSize = $sizes[$si['size']] ?? 30;

            // Spacing → padding the .social-icon class uses
            $spacings = ['tight' => 4, 'normal' => 10, 'loose' => 18];
            $siGap = $spacings[$si['spacing']] ?? 10;

            // Color resolution
            $siColorMode = $si['color'];
            $siCustom = $si['color_custom'] ?? '#111111';
        @endphp

        /* Sizing — applies to every variant of the color/bg modes. */
        .social-icon {
            font-size: {{ $siSize }}px !important;
            padding: {{ max((int) round($siGap / 2), 2) }}px !important;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease !important;
        }
        .social-icon-div {
            gap: {{ $siGap }}px;
            padding-bottom: 30px;
        }
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @if($si['background_style'] === 'circle' || $si['background_style'] === 'rounded')
        /* Container chip — the icon sits in a tinted square / circle. */
        .social-link {
            background: rgba(128, 128, 128, 0.12);
            border-radius: {{ $si['background_style'] === 'circle' ? '50%' : '12px' }};
            padding: 6px;
            width: {{ $siSize + 24 }}px;
            height: {{ $siSize + 24 }}px;
        }
        @elseif($si['background_style'] === 'solid')
        /* Solid filled-color circle — bg color = brand color, glyph
           becomes white. Per-brand rules below override the background. */
        .social-link {
            background: #555;
            color: #fff !important;
            border-radius: 50%;
            padding: 6px;
            width: {{ $siSize + 24 }}px;
            height: {{ $siSize + 24 }}px;
        }
        .social-link .social-icon {
            color: #fff !important;
        }
        @endif

        {{-- Color mode rules --}}
        @if($siColorMode === 'custom')
        .social-icon, .social-link .social-icon {
            color: {{ $siCustom }} !important;
        }
        @endif

        @if($siColorMode === 'brand' || $si['background_style'] === 'solid')
        /* Per-brand color rules — drive the glyph color in Brand mode,
           OR the background-color of the chip in Solid Circle mode. */
        @foreach(\App\Http\Controllers\AppearanceController::BRAND_COLORS as $brand => $hex)
            @if($siColorMode === 'brand' && $si['background_style'] !== 'solid')
        .social-icon.fa-{{ $brand }} {
            color: {{ $hex }} !important;
        }
            @endif
            @if($si['background_style'] === 'solid')
        .social-link:has(.social-icon.fa-{{ $brand }}) {
            background: {{ $hex }} !important;
        }
            @endif
        @endforeach
        @endif

        {{-- Hover effect --}}
        @switch($si['hover'])
            @case('lift')
        .social-link:hover { transform: translateY(-3px); }
                @break
            @case('glow')
        .social-link:hover { box-shadow: 0 0 14px rgba(var(--user-primary-rgb, 59, 130, 246), 0.5); }
                @break
            @case('scale')
        .social-link:hover { transform: scale(1.15); }
                @break
            @case('colorshift')
        .social-link:hover .social-icon { filter: hue-rotate(45deg) saturate(1.3); }
                @break
        @endswitch
    </style>
@endif
