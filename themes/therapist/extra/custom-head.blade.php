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

body::before {
  content: "";
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23161616'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M15.5%2013a3.5%203.5%200%200%200%20-3.5%203.5v1a3.5%203.5%200%200%200%207%200v-1.8'%2F%3E%3Cpath%20d%3D'M8.5%2013a3.5%203.5%200%200%201%203.5%203.5v1a3.5%203.5%200%200%201%20-7%200v-1.8'%2F%3E%3Cpath%20d%3D'M17.5%2016a3.5%203.5%200%200%200%200%20-7h-.5'%2F%3E%3Cpath%20d%3D'M19%209.3v-2.8a3.5%203.5%200%200%200%20-7%200'%2F%3E%3Cpath%20d%3D'M6.5%2016a3.5%203.5%200%200%201%200%20-7h.5'%2F%3E%3Cpath%20d%3D'M5%209.3v-2.8a3.5%203.5%200%200%201%207%200v10'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
