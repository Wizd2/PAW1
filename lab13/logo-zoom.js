// logo-zoom.js — ClickClick
// Requires jQuery

$(function () {
  $('.logo').on('click', function () {
    $(this).toggleClass('logo--zoom');
    $('.logo-overlay').fadeToggle(200);
  });

  $('.logo-overlay').on('click', function () {
    $('.logo').removeClass('logo--zoom');
    $(this).fadeOut(200);
  });
});