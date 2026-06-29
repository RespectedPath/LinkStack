<style>
@font-face {
  font-family: 'Fraunces';
  font-style: normal;
  font-weight: 100 900;
  font-display: swap;
  src: url("{{ themeAsset('fraunces-variable.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Nunito Sans';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url("{{ themeAsset('nunito-sans-400.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Nunito Sans';
  font-style: normal;
  font-weight: 700;
  font-display: swap;
  src: url("{{ themeAsset('nunito-sans-700.woff2') }}") format('woff2');
}

body {
  background-image: linear-gradient(180deg, rgba(255,250,242,0.62), rgba(250,243,232,0.84)), url("{{ themeAsset('yoga-instructor.webp') }}");
  background-size: cover, cover;
  background-position: center, center;
  background-attachment: fixed, fixed;
  background-repeat: no-repeat, no-repeat;
}
</style>
