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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23161616'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M4%205a2%202%200%200%201%202%20-2h12a2%202%200%200%201%202%202v14a2%202%200%200%201%20-2%202h-12a2%202%200%200%201%20-2%20-2l0%20-14'%2F%3E%3Cpath%20d%3D'M8%208a1%201%200%200%201%201%20-1h6a1%201%200%200%201%201%201v1a1%201%200%200%201%20-1%201h-6a1%201%200%200%201%20-1%20-1l0%20-1'%2F%3E%3Cpath%20d%3D'M8%2014l0%20.01'%2F%3E%3Cpath%20d%3D'M12%2014l0%20.01'%2F%3E%3Cpath%20d%3D'M16%2014l0%20.01'%2F%3E%3Cpath%20d%3D'M8%2017l0%20.01'%2F%3E%3Cpath%20d%3D'M12%2017l0%20.01'%2F%3E%3Cpath%20d%3D'M16%2017l0%20.01'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
