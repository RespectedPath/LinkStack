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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23FFFFFF'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M2%2012h1'%2F%3E%3Cpath%20d%3D'M6%208h-2a1%201%200%200%200%20-1%201v6a1%201%200%200%200%201%201h2'%2F%3E%3Cpath%20d%3D'M6%207v10a1%201%200%200%200%201%201h1a1%201%200%200%200%201%20-1v-10a1%201%200%200%200%20-1%20-1h-1a1%201%200%200%200%20-1%201'%2F%3E%3Cpath%20d%3D'M9%2012h6'%2F%3E%3Cpath%20d%3D'M15%207v10a1%201%200%200%200%201%201h1a1%201%200%200%200%201%20-1v-10a1%201%200%200%200%20-1%20-1h-1a1%201%200%200%200%20-1%201'%2F%3E%3Cpath%20d%3D'M18%208h2a1%201%200%200%201%201%201v6a1%201%200%200%201%20-1%201h-2'%2F%3E%3Cpath%20d%3D'M22%2012h-1'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.05;
}
</style>
