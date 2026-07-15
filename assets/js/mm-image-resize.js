/* Shared client-side image resize.
 *
 * One promise, kept everywhere: if a user picks a photo, WE make it fit —
 * dimension cap + encoding chosen so the file always lands under the
 * server's 2MB upload cap.
 *
 * Transparency: JPEG has no alpha channel, and a bare canvas-to-JPEG
 * export collapses transparent pixels to BLACK (the canvas backing
 * store). Sources that actually use transparency (logo PNGs etc.) are
 * therefore exported as PNG — keeping the transparency — and only
 * flattened (onto WHITE, the conventional base) when a transparent
 * image is photographic enough that its PNG would blow the size cap.
 *
 * Used by the studio Appearance background uploader (appearance.js),
 * the avatar cropper (alpha probe), and the admin user editor. Change
 * resize behavior HERE, not per-page.
 */

/* Does this image actually use transparency? Probes a 64px downscale
 * instead of scanning megapixels. `type` short-circuits formats that
 * can't carry alpha at all (JPEG). */
window.mmImageHasAlpha = function (imgEl, type) {
    if (type && !/png|webp|gif|svg/i.test(type)) return false;
    try {
        var probe = document.createElement('canvas');
        probe.width = probe.height = 64;
        var pctx = probe.getContext('2d');
        pctx.drawImage(imgEl, 0, 0, 64, 64);
        var d = pctx.getImageData(0, 0, 64, 64).data;
        for (var i = 3; i < d.length; i += 4) {
            if (d[i] < 250) return true;
        }
    } catch (_) { /* tainted canvas etc. — treat as opaque */ }
    return false;
};

window.mmResizeImage = function (file, opts) {
    opts = opts || {};
    var MAX_DIM   = opts.maxDim || 1920;
    var SIZE_CAP  = (opts.sizeCapKB || 1900) * 1024; // margin under the 2MB rule
    var QUALITIES = opts.qualities || [0.82, 0.7, 0.6, 0.5];
    var baseName  = (opts.name || 'image.jpg').replace(/\.[^.]+$/, '');

    return new Promise(function (resolve, reject) {
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
            var ctx = canvas.getContext('2d');

            function flattenToJpeg() {
                // White base, then the image — transparent regions become
                // white (never black), opaque images are unaffected.
                ctx.clearRect(0, 0, w, h);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);
                (function encodeAt(qi) {
                    canvas.toBlob(function (blob) {
                        if (!blob) return reject(new Error('Render failed'));
                        if (blob.size > SIZE_CAP && qi + 1 < QUALITIES.length) {
                            return encodeAt(qi + 1);
                        }
                        resolve(new File([blob], baseName + '.jpg', { type: 'image/jpeg' }));
                    }, 'image/jpeg', QUALITIES[qi]);
                })(0);
            }

            if (window.mmImageHasAlpha(img, file.type)) {
                ctx.drawImage(img, 0, 0, w, h);
                canvas.toBlob(function (blob) {
                    if (blob && blob.size <= SIZE_CAP) {
                        return resolve(new File([blob], baseName + '.png', { type: 'image/png' }));
                    }
                    // A transparent-but-photographic image whose PNG busts
                    // the cap — flatten it rather than fail the upload.
                    flattenToJpeg();
                }, 'image/png');
                return;
            }

            flattenToJpeg();
        };
        img.onerror = function () {
            URL.revokeObjectURL(url);
            // Most common causes: a cloud-drive "online-only" stub whose
            // bytes aren't local yet, or a format the browser can't
            // decode (HEIC). Tell the user something they can act on.
            reject(new Error('Couldn\'t read that image. If it\'s in a cloud drive (Google Drive, iCloud, OneDrive), download it to your device first. HEIC photos need converting to JPG.'));
        };
        img.src = url;
    });
};
