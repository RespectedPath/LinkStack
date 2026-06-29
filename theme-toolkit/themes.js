// Theme catalogue. Each row is one shipped theme.
// archetype + accent + treatment is all it takes; the generator does the rest.
// treatment: 'solid' | 'texture' | 'photo'. Photo themes need a `background`
// file in theme-toolkit/backgrounds/ and ship it self-hosted.

module.exports = [
  // ---- Pilots (one per archetype) ----------------------------------------
  {
    slug: 'plumber', name: 'Plumber', category: 'Trade', archetype: 'trade',
    accent: '#FF6B2C', treatment: 'solid',
    blurb: 'Bold, high-contrast dark theme for plumbers and contractors who want to look dependable.',
  },
  {
    slug: 'hairstylist', name: 'Hairstylist', category: 'Beauty', archetype: 'bloom',
    accent: '#E8A6A1', treatment: 'texture', texture: 'weave',
    blurb: 'Soft, airy light theme with a woven backdrop — made for salons and stylists.',
  },
  {
    slug: 'lawyer', name: 'Lawyer', category: 'Professional', archetype: 'studio',
    accent: '#7B2D3A', treatment: 'solid',
    blurb: 'Refined editorial theme that reads credible and established for legal and advisory work.',
  },
  {
    slug: 'bakery', name: 'Bakery', category: 'Food', archetype: 'hearth',
    accent: '#D2683E', treatment: 'photo', background: 'bakery.webp', scrim: 'light',
    blurb: 'Warm, appetizing theme with a photographic backdrop for bakeries and cafés.',
  },
  {
    slug: 'tattoo-artist', name: 'Tattoo Artist', category: 'Creative', archetype: 'gallery',
    accent: '#FF2D78', treatment: 'photo', background: 'tattoo.webp', scrim: 'dark',
    blurb: 'Vivid dark gallery theme with a studio backdrop for tattoo artists and creatives.',
  },
];
