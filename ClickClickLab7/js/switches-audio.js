// Lab2 (v1.1+) — ClickClick
// switches-audio.js — odsłuch próbek dźwięku switchy

(function () {
  let current = null;

  function stopCurrent() {
    if (current) {
      current.pause();
      current.currentTime = 0;
      current = null;
    }
  }

  function play(src) {
    stopCurrent();
    current = new Audio(src);
    current.play().catch(() => {
      // W niektórych przeglądarkach autoplay może być blokowany — klik użytkownika zwykle rozwiązuje problem.
    });
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-sound]');
    if (!btn) return;

    e.preventDefault();

    const file = btn.getAttribute('data-sound');
    if (!file) return;

    // Jeśli w tym samym czasie klikniemy ponownie — zatrzymaj.
    const isPlaying = btn.getAttribute('data-playing') === '1';
    document.querySelectorAll('[data-sound][data-playing="1"]').forEach(b => b.setAttribute('data-playing', '0'));

    if (isPlaying) {
      stopCurrent();
      btn.setAttribute('data-playing', '0');
      return;
    }

    btn.setAttribute('data-playing', '1');
    play(file);

    if (current) {
      current.addEventListener('ended', () => {
        btn.setAttribute('data-playing', '0');
      }, { once: true });
    }
  });
})();
