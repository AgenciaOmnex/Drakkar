/* ════════════════════════════════════════════════
   Drakkar Box — Legal Pages JavaScript
   ════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {
  const btnRechazar = document.querySelector('.btn-rechazar');
  if (btnRechazar) {
    btnRechazar.addEventListener('click', function () {
      localStorage.removeItem('dk_cookie_consent');
      window.location.reload();
    });
  }
});
