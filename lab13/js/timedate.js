// (v1.2) — ClickClick
// timedate.js — wyświetlanie daty i czasu

(function () {
  function pad(n) {
    return String(n).padStart(2, '0');
  }

  function updateDateTime() {
    const now = new Date();
    // Format PL: dd.mm.rrrr
    const date = `${pad(now.getDate())}.${pad(now.getMonth() + 1)}.${now.getFullYear()}`;
    const time = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

    const el = document.getElementById('datetime');
    if (el) {
      el.textContent = `Data: ${date} • Godzina: ${time}`;
    }
  }

  // In case script loads before the element exists:
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      updateDateTime();
      setInterval(updateDateTime, 1000);
    });
  } else {
    updateDateTime();
    setInterval(updateDateTime, 1000);
  }
})();
