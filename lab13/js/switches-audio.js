// ClickClick — switches-audio.js
// Odsłuch próbek dźwięku switchy (przycisk z atrybutem data-sound="audio/*.wav")

(function () {
  let current = null;

  function stopCurrent() {
    if (!current) return;
    current.pause();
    current.currentTime = 0;
    current = null;
  }

  function play(src) {
    stopCurrent();
    current = new Audio(src);
    // Play tylko po kliknięciu użytkownika (spełnia wymagania większości przeglądarek)
    current.play().catch(function () {
      // Jeśli przeglądarka blokuje odtwarzanie, klik ponownie zwykle rozwiązuje problem.
    });
  }

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-sound]');
    if (!btn) return;

    e.preventDefault();

    const file = btn.getAttribute('data-sound');
    if (!file) return;

    // Zatrzymaj wszystkie oznaczone jako "grające"
    document
      .querySelectorAll('[data-sound][data-playing="1"]')
      .forEach(function (b) {
        b.setAttribute('data-playing', '0');
      });

    // Jeśli klikamy ten sam przycisk drugi raz — zatrzymaj
    const isPlaying = btn.getAttribute('data-playing') === '1';
    if (isPlaying) {
      stopCurrent();
      btn.setAttribute('data-playing', '0');
      return;
    }

    btn.setAttribute('data-playing', '1');
    play(file);

    if (current) {
      current.addEventListener(
        'ended',
        function () {
          btn.setAttribute('data-playing', '0');
        },
        { once: true }
      );
    }
  });
})();
