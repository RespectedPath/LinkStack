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
  background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg'%20viewBox%3D'0%200%2024%2024'%20fill%3D'none'%20stroke%3D'%23161616'%20stroke-width%3D'1.5'%20stroke-linecap%3D'round'%20stroke-linejoin%3D'round'%3E%3Cpath%20d%3D'M0%200h24v24H0z'%2F%3E%3Cpath%20d%3D'M16.555%203.843l3.602%203.602a2.877%202.877%200%200%201%200%204.069l-2.643%202.643a2.877%202.877%200%200%201%20-4.069%200l-.301%20-.301l-6.558%206.558a2%202%200%200%201%20-1.239%20.578l-.175%20.008h-1.172a1%201%200%200%201%20-.993%20-.883l-.007%20-.117v-1.172a2%202%200%200%201%20.467%20-1.284l.119%20-.13l.414%20-.414h2v-2h2v-2l2.144%20-2.144l-.301%20-.301a2.877%202.877%200%200%201%200%20-4.069l2.643%20-2.643a2.877%202.877%200%200%201%204.069%200'%2F%3E%3Cpath%20d%3D'M15%209h.01'%2F%3E%3C%2Fsvg%3E");
  background-repeat: repeat;
  background-position: center;
  background-size: 78px;
  opacity: 0.06;
}
</style>
