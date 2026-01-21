// Lab3 (v1.2) — ClickClick — jQuery animations

/*
  Wymagane animacje (Lab3):
  1) powiększający się obiekt po kliknięciu
  2) powiększający się obiekt po najechaniu kursorem (hover)
  3) powiększający się z każdym kliknięciem obiekt

  Uwaga: jQuery musi być załadowane w <head>.
*/

$(function () {
  // (1) Click: "pulse" (quick grow + back) — hero image on the main page
  $('.heroVisual, .jqPulseOnClick').on('click', function () {
    const $el = $(this);
    // animate padding to create a grow effect without breaking layout
    $el.stop(true, false)
      .animate({ paddingTop: '+=6px', paddingRight: '+=6px', paddingBottom: '+=6px', paddingLeft: '+=6px' }, 130)
      .animate({ paddingTop: '-=6px', paddingRight: '-=6px', paddingBottom: '-=6px', paddingLeft: '-=6px' }, 130);
  });

  // (2) Hover: gently lift product cards
  $('.productCard').hover(
    function () {
      $(this).stop(true, false).animate({ marginTop: '-=6px' }, 120);
    },
    function () {
      $(this).stop(true, false).animate({ marginTop: '+=6px' }, 120);
    }
  );

  // (3) Each click: grow a dedicated demo box
  const $grow = $('#jqGrowBox');
  if ($grow.length) {
    $grow.on('click', function () {
      $(this).stop(true, false).animate({ width: '+=12px', height: '+=6px' }, 140);
    });
  }
});
