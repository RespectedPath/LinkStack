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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23FFFFFF'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M3%2010v6'%2F%3E%3Cpath%20d%3D'M12%205v3'%2F%3E%3Cpath%20d%3D'M10%205h4'%2F%3E%3Cpath%20d%3D'M5%2013h-2'%2F%3E%3Cpath%20d%3D'M6%2010h2l2%20-2h3.382a1%201%200%200%201%20.894%20.553l1.448%202.894a1%201%200%200%200%20.894%20.553h1.382v-2h2a1%201%200%200%201%201%201v6a1%201%200%200%201%20-1%201h-2v-2h-3v2a1%201%200%200%201%20-1%201h-3.465a1%201%200%200%201%20-.832%20-.445l-1.703%20-2.555h-2v-6'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.05;
}
</style>
