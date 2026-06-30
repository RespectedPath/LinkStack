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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%232B2622'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M4%2011h16a1%201%200%200%201%201%201v.5c0%201.5%20-2.517%205.573%20-4%206.5v1a1%201%200%200%201%20-1%201h-8a1%201%200%200%201%20-1%20-1v-1c-1.687%20-1.054%20-4%20-5%20-4%20-6.5v-.5a1%201%200%200%201%201%20-1'%2F%3E%3Cpath%20d%3D'M12%204a2.4%202.4%200%200%200%20-1%202a2.4%202.4%200%200%200%201%202'%2F%3E%3Cpath%20d%3D'M16%204a2.4%202.4%200%200%200%20-1%202a2.4%202.4%200%200%200%201%202'%2F%3E%3Cpath%20d%3D'M8%204a2.4%202.4%200%200%200%20-1%202a2.4%202.4%200%200%200%201%202'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
