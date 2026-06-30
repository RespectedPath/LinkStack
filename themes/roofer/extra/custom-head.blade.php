<style>
@font-face {
  font-family: 'Oswald';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("{{ themeAsset('oswald-500.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Oswald';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("{{ themeAsset('oswald-600.woff2') }}") format('woff2');
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

body::before {
  content: "";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23FFFFFF'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M5%2012l-2%200l9%20-9l9%209l-2%200'%2F%3E%3Cpath%20d%3D'M5%2012v7a2%202%200%200%200%202%202h10a2%202%200%200%200%202%20-2v-7'%2F%3E%3Cpath%20d%3D'M10%2012h4v4h-4l0%20-4'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.05;
}
</style>
