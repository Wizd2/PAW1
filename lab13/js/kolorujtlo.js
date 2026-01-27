// ClickClick — kolorujtlo.js
// Lab2: zmiana tła strony (przełącznik: bardziej fioletowe -> bardziej zielone)

(function () {
  const themes = [
    {
      name: 'Fiolet',
      background: [
        'radial-gradient(900px 520px at 12% 0%, rgba(124,58,237,.55), transparent 62%)',
        'radial-gradient(900px 520px at 88% 0%, rgba(56,189,248,.14), transparent 60%)',
        'linear-gradient(180deg, #050711, #0b1224 55%, #070a12)'
      ].join(',')
    },
    {
      name: 'Zielony',
      background: [
        'radial-gradient(900px 520px at 12% 0%, rgba(34,197,94,.55), transparent 62%)',
        'radial-gradient(900px 520px at 88% 0%, rgba(16,185,129,.22), transparent 60%)',
        'radial-gradient(700px 520px at 50% 110%, rgba(124,58,237,.10), transparent 55%)',
        'linear-gradient(180deg, #050711, #071a14 55%, #050d0a)'
      ].join(',')
    }
  ];

  let idx = 0;

  function applyTheme(i) {
    document.body.style.background = themes[i].background;
    const label = document.getElementById('themeLabel');
    if (label) label.textContent = themes[i].name;
  }

  // wywoływane z HTML: onclick="changeBackground()"
  window.changeBackground = function () {
    idx = (idx + 1) % themes.length;
    applyTheme(idx);
  };

  // start
  applyTheme(idx);
})();
