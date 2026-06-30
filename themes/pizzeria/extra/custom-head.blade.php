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

body::before {
  content: "";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%232B2622'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M12%2021.5c-3.04%200%20-5.952%20-.714%20-8.5%20-1.983l8.5%20-16.517l8.5%2016.517a19.09%2019.09%200%200%201%20-8.5%201.983'%2F%3E%3Cpath%20d%3D'M5.38%2015.866a14.94%2014.94%200%200%200%206.815%201.634a14.944%2014.944%200%200%200%206.502%20-1.479'%2F%3E%3Cpath%20d%3D'M13%2011.01v-.01'%2F%3E%3Cpath%20d%3D'M11%2014v-.01'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
