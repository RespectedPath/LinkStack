<style>
@font-face {
  font-family: 'Space Grotesk';
  font-style: normal;
  font-weight: 500;
  font-display: swap;
  src: url("{{ themeAsset('space-grotesk-500.woff2') }}") format('woff2');
}
@font-face {
  font-family: 'Space Grotesk';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url("{{ themeAsset('space-grotesk-600.woff2') }}") format('woff2');
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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23FFFFFF'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M12%2021a9%209%200%200%201%200%20-18c4.97%200%209%203.582%209%208c0%201.06%20-.474%202.078%20-1.318%202.828c-.844%20.75%20-1.989%201.172%20-3.182%201.172h-2.5a2%202%200%200%200%20-1%203.75a1.3%201.3%200%200%201%20-1%202.25'%2F%3E%3Cpath%20d%3D'M7.5%2010.5a1%201%200%201%200%202%200a1%201%200%201%200%20-2%200'%2F%3E%3Cpath%20d%3D'M11.5%207.5a1%201%200%201%200%202%200a1%201%200%201%200%20-2%200'%2F%3E%3Cpath%20d%3D'M15.5%2010.5a1%201%200%201%200%202%200a1%201%200%201%200%20-2%200'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.05;
}
</style>
