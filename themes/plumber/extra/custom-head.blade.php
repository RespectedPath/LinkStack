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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23FFFFFF'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M3%2021h4l13%20-13a1.5%201.5%200%200%200%20-4%20-4l-13%2013v4'%2F%3E%3Cpath%20d%3D'M14.5%205.5l4%204'%2F%3E%3Cpath%20d%3D'M12%208l-5%20-5l-4%204l5%205'%2F%3E%3Cpath%20d%3D'M7%208l-1.5%201.5'%2F%3E%3Cpath%20d%3D'M16%2012l5%205l-4%204l-5%20-5'%2F%3E%3Cpath%20d%3D'M16%2017l-1.5%201.5'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.05;
}
</style>
