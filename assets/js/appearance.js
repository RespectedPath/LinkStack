/* Appearance editor — runtime behavior.
   Phase 2 (now): form interactivity (color-picker sync, bg-panel toggle,
   preset quick-click, reset button).
   Phase 4 adds live-preview postMessage here. Kept in one file so the
   eventual preview code lives next to the state it observes. */

(function () {
    'use strict';

    var form = document.getElementById('appearance-form');
    // "wrap" contains the tab nav + all tab panes + the tiny save form.
    // DOM queries for inputs span tabs, so we query from wrap rather
    // than form (inputs live OUTSIDE the form element but are
    // associated via form="appearance-form" attribute).
    var wrap = document.getElementById('appearance-wrap') || form;
    if (!form || !wrap) return;

    // ---------- Color picker <-> hex text input sync ----------

    var pairs = wrap.querySelectorAll('[data-pair]');
    pairs.forEach(function (el) {
        el.addEventListener('input', function () {
            var partner = document.getElementById(el.dataset.pair);
            if (!partner) return;
            var val = el.value;
            // Hex input: uppercase and require 7-char shape before mirroring.
            if (el.classList.contains('appearance-color-hex')) {
                if (/^#[0-9a-fA-F]{6}$/.test(val)) partner.value = val;
            } else {
                partner.value = val;
            }
        });
    });

    // ---------- Color presets (quick-click tiles) ----------

    wrap.querySelectorAll('.appearance-color-preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var hexInput = document.getElementById(btn.dataset.target + '-hex');
            var swatch   = document.getElementById(btn.dataset.target);
            if (hexInput) hexInput.value = btn.dataset.preset;
            if (swatch)   swatch.value   = btn.dataset.preset;
            // Fire input events so the live-preview hook (Phase 4) reacts.
            if (hexInput) hexInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // ---------- Background type → show matching panel ----------

    wrap.querySelectorAll('.appearance-bg-type').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (!radio.checked) return;
            wrap.querySelectorAll('[data-bg-panel]').forEach(function (panel) {
                panel.style.display = (panel.dataset.bgPanel === radio.value) ? '' : 'none';
            });
        });
    });

    // ---------- Social icons: custom color picker visibility ----------
    // Shows the custom color picker only when the "Custom color"
    // radio is active. Same pattern as the background-type panels.

    var socialCustomWrap = document.getElementById('social-custom-color-wrap');
    if (socialCustomWrap) {
        wrap.querySelectorAll('input[name="social_icons[color]"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (!radio.checked) return;
                socialCustomWrap.style.display = (radio.value === 'custom') ? '' : 'none';
            });
        });
    }

    // ---------- Reset button → confirm + submit reset form ----------

    var resetBtn = document.getElementById('appearance-reset-btn');
    var resetForm = document.getElementById('appearance-reset-form');
    if (resetBtn && resetForm) {
        resetBtn.addEventListener('click', function () {
            if (confirm('Reset all appearance customization to defaults? This clears every saved color, font, shape, and background.')) {
                resetForm.submit();
            }
        });
    }

    // ---------- Live preview: iframe + CSS injection on every edit ----------
    //
    // Iframe loads /@{user}?preview=1 (same origin → we can write into
    // its document directly). On any form change we build an override
    // stylesheet and push it into a single <style id="user-appearance-preview">
    // inside the iframe's head. Cascading last beats both the theme
    // CSS and any saved customization-override block.

    var iframe = document.getElementById('appearance-preview-iframe');
    if (!iframe) return;

    var iframeReady = false;

    function readFormState() {
        var fd = new FormData(form);
        var g = function (k) { return fd.get(k) || ''; };
        return {
            colors: {
                primary:     g('colors[primary]'),
                background:  g('colors[background]'),
                text:        g('colors[text]'),
                button_text: g('colors[button_text]'),
            },
            background: {
                type:               g('background[type]'),
                solid:              g('background[solid]'),
                gradient_start:     g('background[gradient_start]'),
                gradient_end:       g('background[gradient_end]'),
                gradient_direction: g('background[gradient_direction]'),
                image_url:          g('background[image_url]'),
            },
            typography: { font: g('typography[font]') },
            buttons:    { shape: g('buttons[shape]'), style: g('buttons[style]') },
            avatar:     { shape: g('avatar[shape]') },
            social_icons: {
                color:            g('social_icons[color]'),
                color_custom:     g('social_icons[color_custom]'),
                size:             g('social_icons[size]'),
                spacing:          g('social_icons[spacing]'),
                background_style: g('social_icons[background_style]'),
                hover:            g('social_icons[hover]'),
            },
        };
    }

    /* Brand colors map — mirrors AppearanceController::BRAND_COLORS.
       Used by the live preview to render per-brand glyph colors in
       Brand Colors mode and per-brand chip backgrounds in Solid
       Filled Circle mode. Keep in sync with the PHP constant. */
    var BRAND_COLORS = {
        'instagram': '#E4405F', 'facebook':  '#1877F2', 'x-twitter': '#000000',
        'github':    '#181717', 'linkedin':  '#0A66C2', 'tiktok':    '#000000',
        'youtube':   '#FF0000', 'threads':   '#000000', 'twitch':    '#9146FF',
        'pinterest': '#E60023', 'snapchat':  '#FFFC00', 'reddit':    '#FF4500',
        'telegram':  '#26A5E4', 'behance':   '#1769FF', 'dribbble':  '#EA4C89',
        'mastodon':  '#6364FF', 'bluesky':   '#0085FF', 'whatsapp':  '#25D366',
        'discord':   '#5865F2'
    };

    function buildSocialIconCss(si) {
        var sizes    = { small: 22, medium: 30, large: 38, xl: 46 };
        var spacings = { tight: 4,  normal: 10, loose: 18 };
        var siSize   = sizes[si.size]    || 30;
        var siGap    = spacings[si.spacing] || 10;
        var pad      = Math.max(Math.round(siGap / 2), 2);
        var custom   = /^#[0-9a-fA-F]{6}$/.test(si.color_custom) ? si.color_custom : '#111111';

        var rules = [
            '.social-icon { font-size: ' + siSize + 'px !important; padding: ' + pad + 'px !important; transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease !important; }',
            '.social-icon-div { gap: ' + siGap + 'px; padding-bottom: 30px; }',
            '.social-link { display: inline-flex; align-items: center; justify-content: center; }'
        ];

        if (si.background_style === 'circle' || si.background_style === 'rounded') {
            rules.push('.social-link { background: rgba(128, 128, 128, 0.12); border-radius: ' +
                (si.background_style === 'circle' ? '50%' : '12px') +
                '; padding: 6px; width: ' + (siSize + 24) + 'px; height: ' + (siSize + 24) + 'px; }');
        } else if (si.background_style === 'solid') {
            rules.push('.social-link { background: #555; color: #fff !important; border-radius: 50%; padding: 6px; width: ' + (siSize + 24) + 'px; height: ' + (siSize + 24) + 'px; }');
            rules.push('.social-link .social-icon { color: #fff !important; }');
        }

        if (si.color === 'custom') {
            rules.push('.social-icon, .social-link .social-icon { color: ' + custom + ' !important; }');
        }

        if (si.color === 'brand' || si.background_style === 'solid') {
            for (var brand in BRAND_COLORS) {
                if (!BRAND_COLORS.hasOwnProperty(brand)) continue;
                var hex = BRAND_COLORS[brand];
                if (si.color === 'brand' && si.background_style !== 'solid') {
                    rules.push('.social-icon.fa-' + brand + ' { color: ' + hex + ' !important; }');
                }
                if (si.background_style === 'solid') {
                    rules.push('.social-link:has(.social-icon.fa-' + brand + ') { background: ' + hex + ' !important; }');
                }
            }
        }

        switch (si.hover) {
            case 'lift':
                rules.push('.social-link:hover { transform: translateY(-3px); }');
                break;
            case 'glow':
                rules.push('.social-link:hover { box-shadow: 0 0 14px rgba(59, 130, 246, 0.5); }');
                break;
            case 'scale':
                rules.push('.social-link:hover { transform: scale(1.15); }');
                break;
            case 'colorshift':
                rules.push('.social-link:hover .social-icon { filter: hue-rotate(45deg) saturate(1.3); }');
                break;
        }

        return rules.join('\n');
    }

    function hexToRgba(hex, alpha) {
        var m = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(hex || '');
        if (!m) return 'rgba(59,130,246,' + alpha + ')';
        return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + alpha + ')';
    }

    function buildOverrideCss(s) {
        // Defensive defaults — if a hidden/inactive sub-panel ever
        // fails to post its value, fall back to valid CSS so the
        // browser doesn't silently drop the whole declaration.
        var HEX = /^#[0-9a-fA-F]{6}$/;
        var accent     = HEX.test(s.colors.primary)     ? s.colors.primary     : '#3b82f6';
        var textColor  = HEX.test(s.colors.text)        ? s.colors.text        : '#111111';
        var btnText    = HEX.test(s.colors.button_text) ? s.colors.button_text : '#ffffff';
        var bgFallback = HEX.test(s.colors.background)  ? s.colors.background  : '#ffffff';
        var solidVal   = HEX.test(s.background.solid)   ? s.background.solid   : bgFallback;
        var gStart     = HEX.test(s.background.gradient_start) ? s.background.gradient_start : '#3b82f6';
        var gEnd       = HEX.test(s.background.gradient_end)   ? s.background.gradient_end   : '#8b5cf6';
        var gDir       = (s.background.gradient_direction && /^to (bottom|right|bottom right)$/.test(s.background.gradient_direction))
                            ? s.background.gradient_direction : 'to bottom';

        // Emit every background-* longhand explicitly. Shorthand lets
        // stale longhand values from the prior saved override block
        // stick around in the cascade — explicit overrides everything.
        var bgCss;
        if (s.background.type === 'gradient') {
            bgCss = "background-image: linear-gradient(" + gDir + ", " + gStart + ", " + gEnd + ") !important;" +
                    "background-color: transparent !important;" +
                    "background-size: auto !important;" +
                    "background-position: 0% 0% !important;" +
                    "background-repeat: no-repeat !important;" +
                    "background-attachment: fixed !important;";
        } else if (s.background.type === 'image' && s.background.image_url) {
            // Accept either http(s):// URLs or local /assets/... paths —
            // both resolve in CSS url() against the iframe's document.
            bgCss = "background-image: url('" + s.background.image_url.replace(/'/g, "\\'") + "') !important;" +
                    "background-size: cover !important;" +
                    "background-position: center !important;" +
                    "background-repeat: no-repeat !important;" +
                    "background-attachment: fixed !important;" +
                    "background-color: " + bgFallback + " !important;";
        } else {
            bgCss = "background-image: none !important;" +
                    "background-color: " + solidVal + " !important;" +
                    "background-size: auto !important;" +
                    "background-position: 0% 0% !important;" +
                    "background-repeat: repeat !important;" +
                    "background-attachment: scroll !important;";
        }

        var shapeRadius  = { pill: '999px', rounded: '10px', square: '0' }[s.buttons.shape] || '10px';
        var avatarHidden = s.avatar.shape === 'off';
        var avatarRadius = s.avatar.shape === 'rounded_square' ? '14px' : '50%';
        // visibility keeps the avatar's layout slot reserved so the
        // links below don't jump up when Off is selected.
        var avatarCss = avatarHidden
            ? '#avatar { visibility: hidden !important; }'
            : '#avatar, .rounded-avatar, img.rounded-avatar { border-radius: ' + avatarRadius + ' !important; }';

        var soft = hexToRgba(accent, 0.15);
        var btnCss;
        if (s.buttons.style === 'filled') {
            btnCss = 'background-color: ' + accent + ' !important; background-image: none !important; color: ' + btnText + ' !important; border: 2px solid ' + accent + ' !important;';
        } else if (s.buttons.style === 'outline') {
            btnCss = 'background-color: transparent !important; background-image: none !important; color: ' + accent + ' !important; border: 2px solid ' + accent + ' !important;';
        } else { // soft
            btnCss = 'background-color: ' + soft + ' !important; background-image: none !important; color: ' + accent + ' !important; border: 2px solid transparent !important;';
        }

        var fontCss = s.typography.font
            ? "body { font-family: '" + s.typography.font + "', -apple-system, BlinkMacSystemFont, sans-serif !important; }"
            : '';

        return [
            ':root { --user-primary: ' + accent + '; --user-bg: ' + bgFallback + '; --user-text: ' + textColor + '; --user-button-text: ' + btnText + '; }',
            'body { ' + bgCss + ' color: ' + textColor + ' !important; }',
            '.header-name, .header-description, h1, h2, h3, h4, h5, h6, p { color: ' + textColor + ' !important; }',
            '.button, .button-custom, .button-custom_website, a.button, .button-default { ' + btnCss + ' border-radius: ' + shapeRadius + ' !important; }',
            avatarCss,
            fontCss,
            buildSocialIconCss(s.social_icons || {})
        ].join('\n');
    }

    function syncPreview() {
        if (!iframeReady) return;
        var doc;
        try { doc = iframe.contentDocument; } catch (e) { return; }
        if (!doc || !doc.head) return;

        var state = readFormState();

        // Disable the server-rendered saved-state override so the
        // preview block is the single source of appearance rules.
        // Otherwise a prior background-image: url(...) !important in
        // the saved block keeps winning against our linear-gradient()
        // replacement (cascade is equal specificity + both !important;
        // theoretically later-source wins, but empirically this fails
        // — safer to silence the losing side completely).
        var saved = doc.getElementById('user-appearance-override');
        if (saved) saved.disabled = true;

        // Upsert the override style element.
        var styleEl = doc.getElementById('user-appearance-preview');
        if (!styleEl) {
            styleEl = doc.createElement('style');
            styleEl.id = 'user-appearance-preview';
            doc.head.appendChild(styleEl);
        }
        styleEl.textContent = buildOverrideCss(state);

        // Load Google Font for the selected family if any.
        var fontLinkId = 'user-appearance-preview-font';
        var existing = doc.getElementById(fontLinkId);
        if (state.typography.font) {
            var href = 'https://fonts.googleapis.com/css2?family=' +
                encodeURIComponent(state.typography.font).replace(/%20/g, '+') +
                ':wght@400;500;600;700&display=swap';
            if (!existing) {
                existing = doc.createElement('link');
                existing.id = fontLinkId;
                existing.rel = 'stylesheet';
                doc.head.appendChild(existing);
            }
            if (existing.getAttribute('href') !== href) existing.setAttribute('href', href);
        } else if (existing) {
            existing.remove();
        }
    }

    iframe.addEventListener('load', function () {
        iframeReady = true;
        syncPreview();
    });

    // Fire on every input / change so the preview matches the form in
    // real time (colors change within a frame of dragging the picker).
    // Events bubble from inputs across tabs to the shared wrap element
    // (they don't bubble to the <form> element because they live
    // outside it in the tab panes).
    wrap.addEventListener('input',  syncPreview);
    wrap.addEventListener('change', syncPreview);

    // ---------- Unsaved-changes warning ----------
    //
    // Mark dirty on any edit; clear the flag when the user submits the
    // save form or the reset form. beforeunload then asks the browser
    // to show its native "are you sure you want to leave" prompt.

    var dirty = false;
    wrap.addEventListener('input',  function () { dirty = true; });
    wrap.addEventListener('change', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
    if (resetForm) resetForm.addEventListener('submit', function () { dirty = false; });

    window.addEventListener('beforeunload', function (e) {
        if (!dirty) return;
        // Per the HTML spec, modern browsers display a generic message
        // regardless of what we return; we just need to preventDefault
        // and set returnValue for back-compat.
        e.preventDefault();
        e.returnValue = '';
        return '';
    });

    // ---------- Tab state persisted in URL hash ----------
    //
    // When the page reloads after a photo upload / bg upload / save,
    // we want the user returned to the tab they were on. Use the URL
    // hash so it survives navigation and is bookmarkable.

    var tabButtons = wrap.querySelectorAll('[data-bs-toggle="pill"]');
    function activateTabById(targetId) {
        if (!targetId) return false;
        var btn = wrap.querySelector('[data-bs-target="' + targetId + '"]');
        if (!btn || !window.bootstrap || !window.bootstrap.Tab) return false;
        window.bootstrap.Tab.getOrCreateInstance(btn).show();
        return true;
    }
    if (location.hash && location.hash.indexOf('#t-') === 0) {
        activateTabById(location.hash);
    }
    tabButtons.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            var target = btn.getAttribute('data-bs-target');
            if (target) {
                // replaceState so we don't litter browser history with a
                // back-stack entry per tab click.
                history.replaceState(null, '', target);
            }
        });
    });

    // ---------- Background image: resize client-side + upload via fetch ----------
    //
    // Lives in the "image" bg panel inside the main form, but the
    // buttons are type="button" so clicking them doesn't submit the
    // main save. We resize in-browser to ≤1920px JPEG q=0.82 then POST
    // to a dedicated endpoint. On success we reload so the live
    // preview iframe picks up the new saved state.

    var bgFile      = document.getElementById('bg-file');
    var bgUploadBtn = document.getElementById('bg-upload-btn');
    var bgRemoveBtn = document.getElementById('bg-remove-btn');
    var bgStatus    = document.getElementById('bg-upload-status');

    function bgSetStatus(msg, kind) {
        if (!bgStatus) return;
        bgStatus.textContent = msg || '';
        bgStatus.className   = 'small mt-2 ' + (kind === 'err' ? 'text-danger' : kind === 'ok' ? 'text-success' : 'text-muted');
    }

    function readCsrf() {
        // The main appearance-form always renders an @csrf hidden input.
        var input = document.querySelector('#appearance-form input[name="_token"]');
        return input ? input.value : '';
    }

    function resizeForBackground(file) {
        return new Promise(function (resolve, reject) {
            var MAX_DIM = 1920;
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                var longest = Math.max(img.naturalWidth, img.naturalHeight);
                var ratio = Math.min(1, MAX_DIM / longest);
                var w = Math.round(img.naturalWidth * ratio);
                var h = Math.round(img.naturalHeight * ratio);
                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                canvas.toBlob(function (blob) {
                    if (!blob) return reject(new Error('Render failed'));
                    resolve(new File([blob], 'background.jpg', { type: 'image/jpeg' }));
                }, 'image/jpeg', 0.82);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Couldn\'t read image'));
            };
            img.src = url;
        });
    }

    if (bgUploadBtn && bgFile) {
        bgUploadBtn.addEventListener('click', function () {
            var f = bgFile.files && bgFile.files[0];
            if (!f) { bgSetStatus('Pick a file first.', 'err'); return; }
            bgUploadBtn.disabled = true;
            bgSetStatus('Resizing…');
            resizeForBackground(f).then(function (resized) {
                bgSetStatus('Uploading…');
                var fd = new FormData();
                fd.append('image', resized);
                fd.append('_token', readCsrf());
                return fetch('/studio/appearance/background-image', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
            }).then(function (resp) {
                if (!resp.ok) {
                    bgSetStatus('Upload failed (' + resp.status + ').', 'err');
                    bgUploadBtn.disabled = false;
                    return;
                }
                bgSetStatus('Uploaded, reloading…', 'ok');
                // Skip the unsaved-changes warning on this intentional reload.
                dirty = false;
                location.reload();
            }).catch(function (e) {
                bgSetStatus(e.message || 'Error.', 'err');
                bgUploadBtn.disabled = false;
            });
        });
    }

    if (bgRemoveBtn) {
        bgRemoveBtn.addEventListener('click', function () {
            if (!confirm('Remove the current background image?')) return;
            bgRemoveBtn.disabled = true;
            var fd = new FormData();
            fd.append('_token', readCsrf());
            fetch('/studio/appearance/background-image/remove', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function (resp) {
                if (!resp.ok) {
                    bgSetStatus('Remove failed (' + resp.status + ').', 'err');
                    bgRemoveBtn.disabled = false;
                    return;
                }
                dirty = false;
                location.reload();
            }).catch(function () {
                bgSetStatus('Network error.', 'err');
                bgRemoveBtn.disabled = false;
            });
        });
    }

    // ---------- Profile photo: client-side crop + drag to center ----------
    //
    // User picks a file, we load it into a circular stage that fills on
    // "cover" math (min dimension fits the circle, excess is cropped by
    // overflow:hidden). They can drag to reposition and zoom via slider.
    // On submit we render the same geometry onto a square canvas at a
    // higher output resolution and replace the file input's File with
    // the cropped version — the server never sees the original.

    var photoForm    = document.getElementById('appearance-photo-form');
    var photoFile    = document.getElementById('photo-file');
    var photoStage   = document.getElementById('photo-stage');
    var photoImage   = document.getElementById('photo-stage-image');
    var photoZoom    = document.getElementById('photo-zoom');
    var photoReset   = document.getElementById('photo-reset-btn');
    var photoEdit    = document.getElementById('photo-edit-controls');

    if (!photoForm || !photoFile || !photoStage || !photoImage) return;

    var STAGE_SIZE  = 160;  // must match CSS
    var OUTPUT_SIZE = 512;  // final square JPEG shipped to the server

    // State: the image's displayed size (renderedW/H px) and its
    // top-left position (dx/dy px) inside the stage.
    var photoState = {
        hasNewFile: false,
        naturalW:   0,
        naturalH:   0,
        baseScale:  1,   // scale needed so image's min dimension fills stage
        userScale:  1,   // slider multiplier, 1..3
        dx:         0,
        dy:         0,
    };

    function layoutImage() {
        var s = photoState;
        var renderedW = s.naturalW * s.baseScale * s.userScale;
        var renderedH = s.naturalH * s.baseScale * s.userScale;
        photoImage.style.width  = renderedW + 'px';
        photoImage.style.height = renderedH + 'px';

        // Clamp drag so the image always covers the stage (no gaps at the edges).
        var minX = STAGE_SIZE - renderedW;
        var minY = STAGE_SIZE - renderedH;
        if (s.dx > 0) s.dx = 0;
        if (s.dy > 0) s.dy = 0;
        if (s.dx < minX) s.dx = minX;
        if (s.dy < minY) s.dy = minY;

        photoImage.style.left = s.dx + 'px';
        photoImage.style.top  = s.dy + 'px';
        photoImage.style.transform = '';  // we're driving layout, not transform
    }

    function centerImage() {
        var s = photoState;
        var renderedW = s.naturalW * s.baseScale * s.userScale;
        var renderedH = s.naturalH * s.baseScale * s.userScale;
        s.dx = (STAGE_SIZE - renderedW) / 2;
        s.dy = (STAGE_SIZE - renderedH) / 2;
        layoutImage();
    }

    photoFile.addEventListener('change', function () {
        var f = photoFile.files && photoFile.files[0];
        if (!f) {
            // user cleared the picker: hide the stage again
            photoStage.style.display = 'none';
            photoState.hasNewFile = false;
            if (photoEdit) photoEdit.style.display = 'none';
            return;
        }

        var url = URL.createObjectURL(f);
        var tmp = new Image();
        tmp.onload = function () {
            photoImage.classList.remove('appearance-photo-stage-image-logo');
            photoImage.src = url;
            photoState.hasNewFile = true;
            photoState.naturalW = tmp.naturalWidth;
            photoState.naturalH = tmp.naturalHeight;
            // "cover" the stage: use the larger required scale.
            photoState.baseScale = Math.max(STAGE_SIZE / tmp.naturalWidth, STAGE_SIZE / tmp.naturalHeight);
            photoState.userScale = 1;
            if (photoZoom) photoZoom.value = '100';
            photoStage.style.display = '';   // reveal when a file has been picked
            centerImage();
            photoStage.classList.add('is-draggable');
            if (photoEdit) photoEdit.style.display = '';
        };
        tmp.src = url;
    });

    if (photoZoom) {
        photoZoom.addEventListener('input', function () {
            if (!photoState.hasNewFile) return;
            photoState.userScale = parseFloat(photoZoom.value) / 100;
            layoutImage();
        });
    }
    if (photoReset) {
        photoReset.addEventListener('click', function () {
            if (!photoState.hasNewFile) return;
            photoState.userScale = 1;
            if (photoZoom) photoZoom.value = '100';
            centerImage();
        });
    }

    // Drag handling (pointer events so mouse + touch both work).
    var dragOrigin = null;
    photoStage.addEventListener('pointerdown', function (e) {
        if (!photoState.hasNewFile) return;
        dragOrigin = { x: e.clientX - photoState.dx, y: e.clientY - photoState.dy };
        photoStage.setPointerCapture(e.pointerId);
        photoStage.classList.add('is-dragging');
    });
    photoStage.addEventListener('pointermove', function (e) {
        if (!dragOrigin) return;
        photoState.dx = e.clientX - dragOrigin.x;
        photoState.dy = e.clientY - dragOrigin.y;
        layoutImage();
    });
    var endDrag = function (e) {
        if (!dragOrigin) return;
        dragOrigin = null;
        photoStage.classList.remove('is-dragging');
        try { photoStage.releasePointerCapture(e.pointerId); } catch (_) {}
    };
    photoStage.addEventListener('pointerup', endDrag);
    photoStage.addEventListener('pointercancel', endDrag);

    // Render the stage's visible crop to a canvas and replace the file
    // input's File before letting the form submit.
    photoForm.addEventListener('submit', function (e) {
        if (!photoState.hasNewFile) return; // nothing to transform; native submit catches missing-file via required
        e.preventDefault();

        var ratio = OUTPUT_SIZE / STAGE_SIZE;
        var canvas = document.createElement('canvas');
        canvas.width  = OUTPUT_SIZE;
        canvas.height = OUTPUT_SIZE;
        var ctx = canvas.getContext('2d');

        var s = photoState;
        var rw = s.naturalW * s.baseScale * s.userScale;
        var rh = s.naturalH * s.baseScale * s.userScale;
        ctx.drawImage(photoImage, s.dx * ratio, s.dy * ratio, rw * ratio, rh * ratio);

        canvas.toBlob(function (blob) {
            if (!blob) { photoForm.submit(); return; }
            var originalName = (photoFile.files[0] && photoFile.files[0].name) || 'avatar.jpg';
            var baseName = originalName.replace(/\.[^.]+$/, '');
            var file = new File([blob], baseName + '.jpg', { type: 'image/jpeg' });
            var dt = new DataTransfer();
            dt.items.add(file);
            photoFile.files = dt.files;
            // Now submit natively so the browser does the multipart upload.
            HTMLFormElement.prototype.submit.call(photoForm);
        }, 'image/jpeg', 0.92);
    });
})();
