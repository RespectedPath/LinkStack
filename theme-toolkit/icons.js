// Turn a Tabler outline SVG (in icons/<name>.svg) into a recolored,
// URL-encoded data-URI usable as a CSS background-image watermark.
const fs = require('fs');
const path = require('path');

function iconDataUri(name, color) {
  const file = path.join(__dirname, 'icons', `${name}.svg`);
  if (!fs.existsSync(file)) throw new Error(`Missing icon: ${name}.svg`);
  const raw = fs.readFileSync(file, 'utf8');
  // Keep every stroked path except Tabler's transparent 24x24 clear-rect.
  const paths = [...raw.matchAll(/<path[^>]*\sd="([^"]+)"[^>]*\/>/g)]
    .map(m => m[1])
    .filter(d => d.replace(/\s+/g, '') !== 'M0 0h24v24H0z');
  const inner = paths.map(d => `<path d='${d}'/>`).join('');
  const svg = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='${color}' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'>${inner}</svg>`;
  return 'data:image/svg+xml,' + encodeURIComponent(svg);
}

module.exports = { iconDataUri };
