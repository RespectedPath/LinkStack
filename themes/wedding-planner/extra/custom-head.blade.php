<style>
@font-face {
  font-family: 'Playfair Display';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("{{ themeAsset('playfair-display-500.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Playfair Display';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("{{ themeAsset('playfair-display-600.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("{{ themeAsset('inter-400.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("{{ themeAsset('inter-500.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Inter';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("{{ themeAsset('inter-700.woff2') }}") format('woff2');
}

body {
  background-image: linear-gradient(180deg, rgba(255,250,242,0.62), rgba(250,243,232,0.84)), url("{{ themeAsset('wedding-planner.webp') }}");
  background-size: cover, cover;
  background-position: center, center;
  background-attachment: fixed, fixed;
  background-repeat: no-repeat, no-repeat;
}
</style>
