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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%233A3330'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M3%2014c.83%20.642%202.077%201.017%203.5%201c1.423%20.017%202.67%20-.358%203.5%20-1c.83%20-.642%202.077%20-1.017%203.5%20-1c1.423%20-.017%202.67%20.358%203.5%201'%2F%3E%3Cpath%20d%3D'M8%203a2.4%202.4%200%200%200%20-1%202a2.4%202.4%200%200%200%201%202'%2F%3E%3Cpath%20d%3D'M12%203a2.4%202.4%200%200%200%20-1%202a2.4%202.4%200%200%200%201%202'%2F%3E%3Cpath%20d%3D'M3%2010h14v5a6%206%200%200%201%20-6%206h-2a6%206%200%200%201%20-6%20-6v-5'%2F%3E%3Cpath%20d%3D'M16.746%2016.726a3%203%200%201%200%20.252%20-5.555'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
