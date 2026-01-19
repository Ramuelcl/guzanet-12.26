// resources/js/time.js

/**
 * Actualiza el reloj del footer en formato HH:MM
 * Compatible con la carga de página inicial y navegación SPA
 */
// export function updateFooterTime() {
//   const now = new Date();
//   const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
//     now.getMinutes().toString().padStart(2, '0');

//   const el = document.getElementById('js-clock-time');
//   if (el) {
//     el.innerText = timeStr;
//   }
// }

// // Iniciar el ciclo del reloj
// document.addEventListener('DOMContentLoaded', () => {
//   updateFooterTime();
//   // Sincronización cada 60 segundos
//   setInterval(updateFooterTime, 60000);
// });

// // Soporte para Livewire 3 / Wire:navigate (SPA)
// document.addEventListener('livewire:navigated', () => {
//   updateFooterTime();
// });

document.addEventListener('livewire:navigated', () => {
  const updateClock = () => {
    const clockElement = document.getElementById('footer-clock');
    if (clockElement) {
      const now = new Date();
      clockElement.textContent = now.toLocaleTimeString();
    }
  };

  // Actualizar cada segundo
  setInterval(updateClock, 10000);
  updateClock();
});