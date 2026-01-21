// Lab2 (v1.1) — ClickClick
// kolorujtlo.js — zmiana tła strony (kilka motywów)

(function () {
  const themes = [
    // 1) Violet + Green + Blue
    {
      name: 'Violet/Green',
      background: [
        'radial-gradient(900px 520px at 10% 0%, rgba(124,58,237,.45), transparent 62%)',
        'radial-gradient(900px 520px at 90% 0%, rgba(34,197,94,.25), transparent 60%)',
        'radial-gradient(700px 520px at 50% 110%, rgba(56,189,248,.14), transparent 55%)',
        'linear-gradient(180deg, #050711, #0b1224 55%, #070a12)'
      ].join(',')
    },
    // 2) Cyan + Purple
    {
      name: 'Cyan/Purple',
      background: [
        'radial-gradient(900px 520px at 15% 0%, rgba(56,189,248,.35), transparent 60%)',
        'radial-gradient(900px 520px at 85% 0%, rgba(124,58,237,.35), transparent 62%)',
        'linear-gradient(180deg, #050711, #0b1224 55%, #070a12)'
      ].join(',')
    },
    // 3) Green + Amber
    {
      name: 'Green/Amber',
      background: [
        'radial-gradient(900px 520px at 20% 0%, rgba(34,197,94,.32), transparent 62%)',
        'radial-gradient(900px 520px at 80% 0%, rgba(245,158,11,.22), transparent 60%)',
        'linear-gradient(180deg, #050711, #0b1224 55%, #070a12)'
      ].join(',')
    }
  ];

  let idx = 0;

  function applyTheme(i) {
    document.body.style.background = themes[i].background;
    const label = document.getElementById('themeLabel');
    if (label) label.textContent = themes[i].name;
  }

  // Udostępniamy funkcję globalnie, aby można było wywołać ją w HTML (onclick)
  window.changeBackground = function () {
    idx = (idx + 1) % themes.length;
    applyTheme(idx);
  };

  // Start: ustaw motyw 1 i podpis
  applyTheme(idx);
})();
