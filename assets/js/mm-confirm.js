/* Inline confirm — replaces window.confirm() across the studio.
 *
 * Why: Chrome (and embedded/webview contexts) can silently suppress
 * native dialogs — after a user dismisses a few, confirm() returns
 * false WITHOUT SHOWING ANYTHING, and the guarded button appears to
 * "do nothing" for the rest of the tab's life. The operator hit
 * exactly that on Remove background. An in-page strip can't be
 * suppressed.
 *
 * mmConfirm(anchor, message, onYes[, yesLabel]) renders a small strip
 * right after the anchor element: the message plus Yes/Cancel buttons.
 * Only one strip exists at a time. Cancel (or opening another strip)
 * dismisses it.
 */
window.mmConfirm = function (anchor, message, onYes, yesLabel) {
    var existing = document.querySelector('.mm-confirm-strip');
    if (existing) existing.remove();
    if (!anchor || !anchor.insertAdjacentElement) { return; }

    var strip = document.createElement('div');
    strip.className = 'mm-confirm-strip';
    strip.setAttribute('role', 'alertdialog');
    strip.style.cssText = 'display:flex;align-items:center;gap:8px;flex-wrap:wrap;' +
        'margin-top:8px;padding:8px 10px;border:1px solid rgba(217,83,79,0.4);' +
        'border-radius:8px;font-size:0.85rem;max-width:480px;';

    var text = document.createElement('span');
    text.textContent = message;

    var yes = document.createElement('button');
    yes.type = 'button';
    yes.className = 'btn btn-sm btn-danger';
    yes.textContent = yesLabel || 'Yes, continue';

    var no = document.createElement('button');
    no.type = 'button';
    no.className = 'btn btn-sm btn-outline-secondary';
    no.textContent = 'Cancel';

    yes.addEventListener('click', function () {
        strip.remove();
        onYes();
    });
    no.addEventListener('click', function () {
        strip.remove();
    });

    strip.appendChild(text);
    strip.appendChild(yes);
    strip.appendChild(no);
    anchor.insertAdjacentElement('afterend', strip);
    // Bring the choice into view for anchors near the fold.
    if (strip.scrollIntoView) strip.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
};
