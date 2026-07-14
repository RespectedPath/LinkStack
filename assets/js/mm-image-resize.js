/* Shared client-side image resize.
 *
 * One promise, kept everywhere: if a user picks a photo, WE make it fit —
 * dimension cap + JPEG quality stepping so the encoded file always lands
 * under the server's 2MB upload cap (dense, fine-grained photos can top
 * 2MB at 1920px q0.82; smooth ones never do, which made failures look
 * random to users).
 *
 * Used by the studio Appearance background uploader (appearance.js) and
 * the admin user editor (panel/edit-user). Change resize behavior HERE,
 * not per-page.
 */
window.mmResizeImage = function (file, opts) {
    opts = opts || {};
    var MAX_DIM   = opts.maxDim || 1920;
    var SIZE_CAP  = (opts.sizeCapKB || 1900) * 1024; // margin under the 2MB rule
    var QUALITIES = opts.qualities || [0.82, 0.7, 0.6, 0.5];
    var name      = opts.name || 'image.jpg';

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
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            (function encodeAt(qi) {
                canvas.toBlob(function (blob) {
                    if (!blob) return reject(new Error('Render failed'));
                    if (blob.size > SIZE_CAP && qi + 1 < QUALITIES.length) {
                        return encodeAt(qi + 1);
                    }
                    resolve(new File([blob], name, { type: 'image/jpeg' }));
                }, 'image/jpeg', QUALITIES[qi]);
            })(0);
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
