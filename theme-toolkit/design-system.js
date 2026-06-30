// Mail Minted theme design system.
// Every theme = one archetype + one accent + a background treatment.
// The archetype fixes mode, neutral surfaces, font pairing and button shape;
// the accent is the only colour that changes between sibling themes.

// --- Font pairings (self-hosted woff2 live in theme-toolkit/fonts) ---------
const FONTS = {
  trade:   { heading: 'Oswald',           body: 'Inter',        files: ['oswald-500','oswald-600','inter-400','inter-500','inter-700'], uppercase: true,  headingWeight: 600 },
  warm:    { heading: 'Fraunces',         body: 'Nunito Sans',  files: ['fraunces-variable','nunito-sans-400','nunito-sans-700'],       uppercase: false, headingWeight: 600 },
  editorial:{ heading: 'Playfair Display', body: 'Inter',        files: ['playfair-display-500','playfair-display-600','inter-400','inter-500','inter-700'], uppercase: false, headingWeight: 600 },
  modern:  { heading: 'Space Grotesk',    body: 'Inter',        files: ['space-grotesk-500','space-grotesk-600','inter-400','inter-500','inter-700'], uppercase: false, headingWeight: 600 },
};

// woff2 weights per family, so @font-face blocks point at the right files.
const FONT_FACES = {
  'Oswald':           [['oswald-500',500],['oswald-600',600]],
  'Inter':            [['inter-400',400],['inter-500',500],['inter-700',700]],
  'Fraunces':         [['fraunces-variable','100 900']],
  'Nunito Sans':      [['nunito-sans-400',400],['nunito-sans-700',700]],
  'Playfair Display': [['playfair-display-500',500],['playfair-display-600',600]],
  'Space Grotesk':    [['space-grotesk-500',500],['space-grotesk-600',600]],
};

// --- Button shapes ---------------------------------------------------------
const SHAPES = { sharp: '4px', soft: '14px', pill: '999px' };

// --- Archetypes ------------------------------------------------------------
// `button(accent)` returns the resting button treatment so accent siblings
// are visually distinct in the picker thumbnail, not just on hover.
const ARCHETYPES = {
  trade: {
    label: 'Trade', mode: 'dark', font: 'trade', shape: 'sharp',
    bg: '#14181F', surface: '#1F2733', card: '#1A2029',
    ink: '#FFFFFF', inkSoft: '#AEB7C4', footer: '#11151B',
    button: (a) => ({ bg: '#1F2733', text: '#FFFFFF', border: `3px solid ${a}`, borderSide: 'left' }),
    avatarRing: (a) => a,
  },
  bloom: {
    label: 'Bloom', mode: 'light', font: 'warm', shape: 'pill',
    bg: '#FAF6F2', surface: '#FFFFFF', card: '#FFFFFF',
    ink: '#3A3330', inkSoft: '#7A6F68', footer: '#F2EBE4',
    button: (a) => ({ bg: '#FFFFFF', text: '#3A3330', border: `1.5px solid ${a}`, borderSide: 'all' }),
    avatarRing: (a) => a,
  },
  hearth: {
    label: 'Hearth', mode: 'light', font: 'warm', shape: 'soft',
    bg: '#FFF8EE', surface: '#2B2622', card: '#FFFFFF',
    ink: '#2B2622', inkSoft: '#7C6F63', footer: '#F2E7D6',
    button: (a) => ({ bg: '#2B2622', text: '#FFF8EE', border: `3px solid ${a}`, borderSide: 'bottom' }),
    avatarRing: (a) => a,
  },
  studio: {
    label: 'Studio', mode: 'light', font: 'editorial', shape: 'sharp',
    bg: '#F4F2EE', surface: '#FFFFFF', card: '#FFFFFF',
    ink: '#161616', inkSoft: '#5C5A55', footer: '#EAE7E0',
    button: (a) => ({ bg: '#FFFFFF', text: '#161616', border: `1px solid #161616`, borderSide: 'all', accentBar: a }),
    avatarRing: (a) => a,
  },
  gallery: {
    label: 'Gallery', mode: 'dark', font: 'modern', shape: 'soft',
    bg: '#0D0D0F', surface: '#1A1A1E', card: '#161618',
    ink: '#FFFFFF', inkSoft: '#A0A0A8', footer: '#0A0A0C',
    button: (a) => ({ bg: '#1A1A1E', text: '#FFFFFF', border: `1.5px solid ${a}`, borderSide: 'all' }),
    avatarRing: (a) => a,
  },
};

// Darken/lighten helper for accent-derived text-on-accent etc.
function shade(hex, amt) {
  const n = parseInt(hex.slice(1), 16);
  let r = (n >> 16) + amt, g = ((n >> 8) & 255) + amt, b = (n & 255) + amt;
  r = Math.max(0, Math.min(255, r)); g = Math.max(0, Math.min(255, g)); b = Math.max(0, Math.min(255, b));
  return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

// Resolve a theme spec into the full CSS-variable set + render hints.
function resolve(spec) {
  const a = ARCHETYPES[spec.archetype];
  if (!a) throw new Error(`Unknown archetype: ${spec.archetype}`);
  const shape = SHAPES[spec.shape || a.shape];
  const font = FONTS[a.font];
  const btn = a.button(spec.accent);
  const dark = a.mode === 'dark';
  return {
    slug: spec.slug, name: spec.name, category: spec.category, blurb: spec.blurb,
    archetype: spec.archetype, mode: a.mode, accent: spec.accent,
    icon: spec.icon || null,
    treatment: spec.treatment || 'solid', background: spec.background || null,
    scrim: spec.scrim || (dark ? 'dark' : 'light'),
    font, shape, button: btn,
    headingFont: font.heading, bodyFont: font.body,
    uppercase: font.uppercase, headingWeight: font.headingWeight,
    avatarRing: a.avatarRing(spec.accent),
    vars: {
      '--font-family': `'${font.body}', sans-serif`,
      '--heading-font-family': `'${font.heading}', serif`,
      '--font-size': '16px',
      '--background-color': a.bg,
      '--image-border-color': a.surface,
      '--image-border-px': '3px',
      '--image-width': '130px',
      '--image-height': '130px',
      '--title-color': a.ink,
      '--description-color': a.inkSoft,
      '--accent-color': spec.accent,
      '--svg-color': a.ink,
      '--menu-background-color': a.card,
      '--menu-text-color': a.ink,
      '--menu-active-text-color': spec.accent,
      '--button-background-color': btn.bg,
      '--button-text-color': btn.text,
      '--button-text-hover-color': spec.accent,
      '--textarea-background-color': a.card,
      '--textarea-text-color': a.ink,
      '--textarea-link-text-color': spec.accent,
      '--footer-background-color': a.footer,
      '--footer-text-color': a.inkSoft,
      '--footer-link-text-color': spec.accent,
    },
  };
}

module.exports = { FONTS, FONT_FACES, SHAPES, ARCHETYPES, resolve, shade };
