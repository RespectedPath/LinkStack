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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%233A3330'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M7.502%2019.423c2.602%202.105%206.395%202.105%208.996%200c2.602%20-2.105%203.262%20-5.708%201.566%20-8.546l-4.89%20-7.26c-.42%20-.625%20-1.287%20-.803%20-1.936%20-.397a1.376%201.376%200%200%200%20-.41%20.397l-4.893%207.26c-1.695%202.838%20-1.035%206.441%201.567%208.546'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
