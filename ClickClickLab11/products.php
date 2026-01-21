<?php
// ==========================================
// ClickClick — products.php (Lab11)
// Ten plik odpowiada za wyswietlanie produktow sklepu.
// W Lab11 robimy produkty w bazie danych i wyswietlamy je na stronie.
// ==========================================

// Male helpery (zeby nie powtarzac w kazdym pliku)
function cc_h(string $str): string {
  return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Liczymy brutto na podstawie netto + VAT
function cc_price_brutto(float $netto, int $vat): float {
  return $netto + ($netto * ($vat / 100));
}

// Sprawdzamy, czy produkt jest "dostepny" (proste warunki z opisu lab11)
function cc_is_available(array $p): bool {
  // status 1 = aktywny
  if ((int)$p['status'] !== 1) {
    return false;
  }

  // ilosc sztuk na magazynie
  if ((int)$p['stock_qty'] <= 0) {
    return false;
  }

  // data wygasniecia (jak jest ustawiona)
  if (!empty($p['expires_at'])) {
    $exp = strtotime($p['expires_at']);
    if ($exp !== false && $exp < time()) {
      return false;
    }
  }

  return true;
}

// Pobieramy ID kategorii po nazwie (np. "Switche")
function cc_get_category_id_by_name(string $name): int {
  global $link;

  $nameEsc = mysqli_real_escape_string($link, $name);
  $q = mysqli_query($link, "SELECT id FROM categories WHERE name='$nameEsc' LIMIT 1");
  if (!$q) {
    return 0;
  }
  $row = mysqli_fetch_assoc($q);
  return $row ? (int)$row['id'] : 0;
}

// Render jednej karty produktu (wyglad sklepu)
function cc_render_product_card(array $p): string {
  $title = cc_h($p['title'] ?? '');
  $desc = cc_h($p['description'] ?? '');
  $img = cc_h($p['image_url'] ?? '');

  $netto = (float)$p['price_netto'];
  $vat = (int)$p['vat'];
  $brutto = cc_price_brutto($netto, $vat);

  $available = cc_is_available($p);
  $badge = $available
    ? '<span class="badge"><span class="dot green"></span> Dostępny</span>'
    : '<span class="badge"><span class="dot"></span> Niedostępny</span>';

  // Ikonka glosnika (dla switchy mamy sound_file)
  // W CSS jest klasa .speakerBtn (zrobiona wczesniej przy "kartach produktu")
  $soundBtn = '';
  if (!empty($p['sound_file'])) {
    $sound = cc_h($p['sound_file']);
    $soundBtn = '<button class="speakerBtn" type="button" title="Odsłuch" data-sound="' . $sound . '">🔊</button>';
  }

  // Proste "specy" w 2 kolumnach
  $spec1 = cc_h($p['spec_1'] ?? '');
  $spec2 = cc_h($p['spec_2'] ?? '');
  $spec3 = cc_h($p['spec_3'] ?? '');
  $spec4 = cc_h($p['spec_4'] ?? '');

  $specHtml = '';
  $specPairs = [
    $spec1,
    $spec2,
    $spec3,
    $spec4,
  ];
  $specPairs = array_filter($specPairs, fn($x) => $x !== '');
  if (!empty($specPairs)) {
    $specHtml .= '<div class="specGrid">';
    foreach ($specPairs as $s) {
      $specHtml .= '<div class="spec">' . $s . '</div>';
    }
    $specHtml .= '</div>';
  }

  $imgBlock = $img !== ''
    ? '<div class="productImg"><img src="' . $img . '" alt="' . $title . '" /></div>'
    : '<div class="productImg" style="display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);">Brak zdjęcia</div>';

  return '<article class="productCard cardHover">'
    . '<div class="productHeader">'
      . '<div>'
        . '<h3>' . $title . '</h3>'
        . '<div class="productMeta">' . $badge . '</div>'
      . '</div>'
      . $soundBtn
    . '</div>'
    . $imgBlock
    . '<div class="productBody">'
      . '<p class="productMeta" style="margin:0;">' . $desc . '</p>'
      . $specHtml
      . '<div class="priceRow">'
        . '<div class="price">' . number_format($brutto, 2, ',', ' ') . ' zł</div>'
        . '<span class="smallBtn">netto: ' . number_format($netto, 2, ',', ' ') . ' zł | VAT: ' . $vat . '%</span>'
      . '</div>'
      . '<div class="tagRow">'
        . '<span class="tag">Szczegóły (później)</span>'
        . '<span class="tag" style="opacity:.7;">Koszyk (Lab12)</span>'
      . '</div>'
    . '</div>'
  . '</article>';
}

// Glowna funkcja dla Lab11: wyswietlamy produkty
function PokazProduktySklepu(): string {
  global $link;

  // mapujemy idp -> nazwa glownej kategorii
  $idp = isset($_GET['idp']) ? $_GET['idp'] : '';
  $idp = preg_replace('/[^a-zA-Z0-9_\-]/', '', $idp);

  $catName = '';
  if ($idp === 'switches') $catName = 'Switche';
  if ($idp === 'keycaps') $catName = 'Keycapy';
  if ($idp === 'cables')  $catName = 'Kable';

  // dodatkowo: mozna przekazac cat=ID (np. podkategoria)
  $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
  if ($catId <= 0 && $catName !== '') {
    $catId = cc_get_category_id_by_name($catName);
  }

  if ($catId <= 0) {
    return '<section class="card">'
      . '<h2>Produkty</h2>'
      . '<p>Nie mogę znaleźć kategorii w bazie. Najpierw zaimportuj kategorie (Lab10).</p>'
      . '</section>';
  }

  // pobieramy produkty z tej kategorii
  $q = mysqli_query(
    $link,
    "SELECT id, title, description, price_netto, vat, stock_qty, status, expires_at, image_url, sound_file, spec_1, spec_2, spec_3, spec_4
     FROM products
     WHERE category_id=$catId
     ORDER BY created_at DESC, id DESC"
  );

  if (!$q) {
    return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
      . '<b>Błąd SQL:</b> ' . cc_h(mysqli_error($link))
      . '</div>';
  }

  $cards = '';
  $count = 0;
  while ($p = mysqli_fetch_assoc($q)) {
    $count++;
    $cards .= cc_render_product_card($p);
  }

  if ($count === 0) {
    $cards = '<div class="card" style="background: rgba(255,255,255,.05); border-color: rgba(245,158,11,.35);">'
      . '<b>Brak produktów w tej kategorii.</b><br />'
      . 'Zaimportuj plik <code>lab11_products_template.sql</code> albo dodaj produkty w panelu admina.'
      . '</div>';
  } else {
    $cards = '<div class="productGrid">' . $cards . '</div>';
  }

  return '<section class="card">'
    . '<h2>Produkty</h2>'
    . '<p>Lab11: produkty są w bazie (netto + VAT), a cena brutto jest liczona w PHP.</p>'
    . $cards
    . '</section>';
}
