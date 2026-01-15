// C:\laragon\www\laravel\guzanet-12.26\resources\js\modo-dark.js

// Este archivo ahora solo se encarga de asegurar que la clase esté presente 
// si el usuario refresca la página manualmente.
export function initDarkMode() {
  const theme = localStorage.getItem('theme');
  if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

document.addEventListener('livewire:navigated', initDarkMode);