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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%232B2622'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M19%203v12h-5c-.023%20-3.681%20.184%20-7.406%205%20-12m0%2012v6h-1v-3m-10%20-14v17m-3%20-17v3a3%203%200%201%200%206%200v-3'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
