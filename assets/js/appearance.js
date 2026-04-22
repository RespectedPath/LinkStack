/* Appearance editor — runtime behavior.
   Phase 2 (now): form interactivity (color-picker sync, bg-panel toggle,
   preset quick-click, reset button).
   Phase 4 adds live-preview postMessage here. Kept in one file so the
   eventual preview code lives next to the state it observes. */

(function () {
    'use strict';

    var form = document.getElementById('appearance-form');
    if (!form) return;

    // ---------- Color picker <-> hex text input sync ----------

    var pairs = form.querySelectorAll('[data-pair]');
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

    form.querySelectorAll('.appearance-color-preset').forEach(function (btn) {
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

    form.querySelectorAll('.appearance-bg-type').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (!radio.checked) return;
            form.querySelectorAll('[data-bg-panel]').forEach(function (panel) {
                panel.style.display = (panel.dataset.bgPanel === radio.value) ? '' : 'none';
            });
        });
    });

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
        };
    }

    function hexToRgba(hex, alpha) {
        var m = /^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/.exec(hex || '');
        if (!m) return 'rgba(59,130,246,' + alpha + ')';
        return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + alpha + ')';
    }

    function buildOverrideCss(s) {
        var bgCss;
        if (s.background.type === 'gradient') {
            bgCss = 'background: linear-gradient(' + s.background.gradient_direction + ', ' + s.background.gradient_start + ', ' + s.background.gradient_end + ') !important; background-attachment: fixed !important;';
        } else if (s.background.type === 'image' && /^https?:\/\//i.test(s.background.image_url)) {
            bgCss = "background-image: url('" + s.background.image_url.replace(/'/g, "\\'") + "') !important; background-size: cover !important; background-position: center !important; background-attachment: fixed !important; background-repeat: no-repeat !important; background-color: " + s.colors.background + ' !important;';
        } else {
            bgCss = 'background-color: ' + (s.background.solid || s.colors.background) + ' !important; background-image: none !important;';
        }

        var shapeRadius  = { pill: '999px', rounded: '10px', square: '0' }[s.buttons.shape] || '10px';
        var avatarRadius = s.avatar.shape === 'rounded_square' ? '14px' : '50%';

        var soft = hexToRgba(s.colors.primary, 0.15);
        var btnCss;
        if (s.buttons.style === 'filled') {
            btnCss = 'background-color: ' + s.colors.primary + ' !important; background-image: none !important; color: ' + s.colors.button_text + ' !important; border: 2px solid ' + s.colors.primary + ' !important;';
        } else if (s.buttons.style === 'outline') {
            btnCss = 'background-color: transparent !important; background-image: none !important; color: ' + s.colors.primary + ' !important; border: 2px solid ' + s.colors.primary + ' !important;';
        } else { // soft
            btnCss = 'background-color: ' + soft + ' !important; background-image: none !important; color: ' + s.colors.primary + ' !important; border: 2px solid transparent !important;';
        }

        var fontCss = s.typography.font
            ? "body { font-family: '" + s.typography.font + "', -apple-system, BlinkMacSystemFont, sans-serif !important; }"
            : '';

        return [
            ':root { --user-primary: ' + s.colors.primary + '; --user-bg: ' + s.colors.background + '; --user-text: ' + s.colors.text + '; --user-button-text: ' + s.colors.button_text + '; }',
            'body { ' + bgCss + ' color: ' + s.colors.text + ' !important; }',
            '.header-name, .header-description, h1, h2, h3, h4, h5, h6, p { color: ' + s.colors.text + ' !important; }',
            '.button, .button-custom, .button-custom_website, a.button, .button-default { ' + btnCss + ' border-radius: ' + shapeRadius + ' !important; }',
            '.rounded-avatar, img.rounded-avatar { border-radius: ' + avatarRadius + ' !important; }',
            fontCss
        ].join('\n');
    }

    function syncPreview() {
        if (!iframeReady) return;
        var doc;
        try { doc = iframe.contentDocument; } catch (e) { return; }
        if (!doc || !doc.head) return;

        var state = readFormState();

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
    form.addEventListener('input',  syncPreview);
    form.addEventListener('change', syncPreview);

    // ---------- Unsaved-changes warning ----------
    //
    // Mark the form dirty on any edit; clear the flag when the user
    // submits the save form or the reset form. beforeunload then asks
    // the browser to show its native "are you sure you want to leave"
    // prompt. Reset button already confirms separately, so we clear
    // before submission there.

    var dirty = false;
    form.addEventListener('input',  function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
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
})();
