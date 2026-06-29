#!/usr/bin/env node
// Render each theme as a bio page in headless Chromium -> themes/<slug>/preview.png
// Usage: node previews.js [slug ...]   (no args = all)

const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { resolve, FONT_FACES } = require('./design-system');
const SPECS = require('./themes');

const ROOT = path.resolve(__dirname, '..');
const FONTS_DIR = path.join(__dirname, 'fonts');
const BG_DIR = path.join(__dirname, 'backgrounds');
const fileUrl = (p) => 'file://' + p;

function fontFaces(t) {
  const fams = [...new Set([t.headingFont, t.bodyFont])];
  let out = '';
  for (const fam of fams)
    for (const [file, weight] of FONT_FACES[fam])
      out += `@font-face{font-family:'${fam}';font-weight:${weight};font-display:block;src:url('${fileUrl(path.join(FONTS_DIR, file + '.woff2'))}') format('woff2');}\n`;
  return out;
}

function bodyBackground(t) {
  if (t.treatment === 'photo' && t.background) {
    const src = path.join(BG_DIR, t.background);
    if (fs.existsSync(src)) {
      const scrim = t.scrim === 'dark'
        ? 'linear-gradient(180deg,rgba(8,8,10,.55),rgba(8,8,10,.78))'
        : 'linear-gradient(180deg,rgba(255,250,242,.62),rgba(250,243,232,.84))';
      return `background:${scrim},url('${fileUrl(src)}') center/cover no-repeat;`;
    }
  }
  if (t.treatment === 'texture') {
    const ink = t.mode === 'dark' ? 'rgba(255,255,255,0.035)' : 'rgba(80,55,35,0.05)';
    const pats = {
      weave: `background-image:repeating-linear-gradient(45deg,${ink} 0 2px,transparent 2px 10px),repeating-linear-gradient(-45deg,${ink} 0 2px,transparent 2px 10px);`,
      dots: `background-image:radial-gradient(${ink} 1.2px,transparent 1.3px);background-size:16px 16px;`,
      grid: `background-image:linear-gradient(${ink} 1px,transparent 1px),linear-gradient(90deg,${ink} 1px,transparent 1px);background-size:22px 22px;`,
    };
    return `background-color:${t.vars['--background-color']};${pats[t.texture || 'dots']}`;
  }
  return `background-color:${t.vars['--background-color']};`;
}

function buttonBorder(t) {
  const b = t.button;
  const base = t.mode === 'dark' ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
  if (b.borderSide === 'all') return `border:${b.border};${b.accentBar ? `border-left:4px solid ${b.accentBar};` : ''}`;
  if (b.borderSide === 'left') return `border:1px solid ${base};border-left:${b.border};`;
  return `border:1px solid ${base};border-bottom:${b.border};`;
}

const SAMPLE_NAMES = {
  plumber: 'Rivera Plumbing', hairstylist: 'Shear Studio', lawyer: 'Marsh & Co.',
  bakery: 'The Corner Bakery', 'tattoo-artist': 'Nyx Tattoo', photographer: 'Aperture Studio',
  restaurant: 'The Oak Table', 'coffee-shop': 'Daybreak Coffee', florist: 'Wildbloom',
  'wedding-planner': 'Evergreen Events', 'travel-creator': 'Far & Away', spa: 'Stillwater Spa',
  'yoga-instructor': 'Lotus Flow', musician: 'Echo & Oak', barber: 'The Cut Room',
};

function html(t) {
  const demoName = SAMPLE_NAMES[t.slug] || t.name;
  const labels = ['Book an appointment', 'Our services', 'Reviews', 'Call or text us'];
  const btns = labels.map(l =>
    `<a class="button">${l}</a>`).join('');
  const icons = ['', '', '', ''].map(() =>
    `<span class="soc"></span>`).join('');
  return `<!doctype html><html><head><meta charset="utf-8"><style>
${fontFaces(t)}
*{box-sizing:border-box;margin:0;padding:0;}
html,body{width:720px;}
body{${bodyBackground(t)}font-family:'${t.bodyFont}',sans-serif;color:${t.vars['--title-color']};min-height:1040px;padding:64px 60px 48px;text-align:center;}
.av{width:128px;height:128px;border-radius:50%;margin:0 auto 22px;background:${t.button.bg};box-shadow:0 0 0 3px ${t.avatarRing};}
h1{font-family:'${t.headingFont}',serif;font-weight:${t.headingWeight};font-size:38px;letter-spacing:${t.uppercase ? '0.16em' : '0.01em'};text-transform:${t.uppercase ? 'uppercase' : 'none'};color:${t.vars['--title-color']};margin-bottom:10px;line-height:1.12;}
p.tag{color:${t.vars['--description-color']};font-size:18px;margin-bottom:34px;}
.button{display:flex;align-items:center;justify-content:center;min-height:58px;width:100%;max-width:600px;margin:0 auto 16px;border-radius:${t.shape};background:${t.button.bg};color:${t.button.text};font-weight:500;font-size:17px;${buttonBorder(t)}}
.socs{margin-top:30px;display:flex;gap:18px;justify-content:center;}
.soc{width:34px;height:34px;border-radius:50%;background:${t.vars['--description-color']};opacity:.55;}
</style></head><body>
<div class="av"></div>
<h1>${demoName}</h1>
<p class="tag">${t.category} · book online anytime</p>
${btns}
<div class="socs">${icons}</div>
</body></html>`;
}

(async () => {
  const want = process.argv.slice(2);
  const specs = (want.length ? SPECS.filter(s => want.includes(s.slug)) : SPECS);
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 720, height: 1040 }, deviceScaleFactor: 2 });
  const tmp = path.join(__dirname, '.preview.html');
  for (const spec of specs) {
    const t = resolve(spec);
    const out = path.join(ROOT, 'themes', t.slug, 'preview.png');
    if (!fs.existsSync(path.dirname(out))) { console.warn(`  ! ${t.slug}: build the theme first`); continue; }
    fs.writeFileSync(tmp, html(t));
    await page.goto(fileUrl(tmp), { waitUntil: 'networkidle' });
    await page.evaluate(async () => { await document.fonts.ready; });
    await page.screenshot({ path: out, fullPage: true });
    console.log(`✓ preview ${t.slug}`);
  }
  fs.existsSync(tmp) && fs.unlinkSync(tmp);
  await browser.close();
})();
