// Lab2 (v1.1) — ClickClick
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

  updateDateTime();
  setInterval(updateDateTime, 1000);
})();
