<?php
// ==========================================
// ClickClick — products.php 
// Ten plik odpowiada za wyswietlanie produktow sklepu.
// W robimy produkty w bazie danych i wyswietlamy je na stronie.
// ==========================================

// Dolaczamy helpery (require_once, zeby nie bylo "Cannot redeclare")
require_once __DIR__. '/helpers.php';

// Sprawdzamy, czy produkt jest "dostepny" (proste warunki z opisu )
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
 

// Zwraca liste ID: wybrana kategoria + wszystkie podkategorie (rekurencja)
function cc_get_category_descendant_ids(int $catId): array {
  global $link;
  $catId = (int)$catId;
  if ($catId <= 0) return [];

  $ids = [$catId];
  $q = mysqli_query($link, "SELECT id FROM categories WHERE parent_id=$catId ORDER BY id ASC");
  if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
      $childId = (int)$r['id'];
      if ($childId > 0) {
        foreach (cc_get_category_descendant_ids($childId) as $x) {
          $ids[] = (int)$x;
        }
      }
    }
  }
  $ids = array_values(array_unique(array_map('intval', $ids)));
  return $ids;
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
 // W CSS jest klasa.speakerBtn (zrobiona wczesniej przy "kartach produktu")
 $soundBtn = '';
 if (!empty($p['sound_file'])) {
 $sound = cc_h($p['sound_file']);
  $soundBtn = '<button class="speakerBtn" type="button" title="Odsłuch" data-sound="'. $sound. '" data-playing="0">🔊</button>';
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
 $specHtml.= '<div class="specGrid">';
 foreach ($specPairs as $s) {
 $specHtml.= '<div class="spec">'. $s. '</div>';
 }
 $specHtml.= '</div>';
 }

 $imgBlock = $img !== ''
 ? '<div class="productImg"><img src="'. $img. '" alt="'. $title. '" /></div>'
 : '<div class="productImg" style="display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);">Brak zdjęcia</div>';

 // : przycisk "Dodaj do koszyka" (koszyk jest na sesji)
 // Robimy prosty formularz POST, zeby bylo czytelnie i bez kombinowania.
 $pid = (int)($p['id'] ?? 0);
 $cartForm = '';
 if ($pid > 0) {
 if ($available) {
 $cartForm = '<form class="cartForm" method="post" action="index.php?idp=cart&action=add">'
. '<input type="hidden" name="pid" value="'. $pid. '" />'
. '<input type="hidden" name="qty" value="1" />'
. '<button class="cartBtn" type="submit">Dodaj do koszyka</button>'
. '</form>';
 } else {
 $cartForm = '<div class="cartForm">'
. '<button class="cartBtn" type="button" disabled>Brak na stanie</button>'
. '</div>';
 }
 }

 return '<article class="productCard cardHover">'
. '<div class="productHeader">'
. '<div>'
. '<h3>'. $title. '</h3>'
. '<div class="productMeta">'. $badge. '</div>'
. '</div>'
. $soundBtn
. '</div>'
. $imgBlock
. '<div class="productBody">'
. '<p class="productMeta" style="margin:0;">'. $desc. '</p>'
. $specHtml
. '<div class="priceRow">'
. '<div class="price">'. number_format($brutto, 2, ',', ' '). ' zł</div>'
. '<span class="smallBtn">netto: '. number_format($netto, 2, ',', ' '). ' zł | VAT: '. $vat. '%</span>'
. '</div>'
. '<div class="tagRow">'
. '<span class="tag">Szczegóły (później)</span>'
. '<span class="tag" style="opacity:.7;">Cena brutto z VAT</span>'
. '</div>'
. $cartForm
. '</div>'
. '</article>';
}

// Glowna funkcja dla : wyswietlamy produkty
function PokazProduktySklepu(): string {
 global $link;

 // mapujemy idp -> nazwa glownej kategorii
 $idp = isset($_GET['idp']) ? $_GET['idp'] : '';
 $idp = preg_replace('/[^a-zA-Z0-9_\-]/', '', $idp);

 $catName = '';
 if ($idp === 'switches') $catName = 'Switche';
 if ($idp === 'keycaps') $catName = 'Keycapy';
 if ($idp === 'cables') $catName = 'Kable';

 // dodatkowo: mozna przekazac cat=ID (np. podkategoria)
 $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
 if ($catId <= 0 && $catName !== '') {
 $catId = cc_get_category_id_by_name($catName);
 }

 if ($catId <= 0) {
 return '<section class="card">'
. '<h2>Produkty</h2>'
. '<p>Nie mogę znaleźć kategorii w bazie. Najpierw zaimportuj kategorie.</p>'
. '</section>';
 }

 

 // pobieramy ID kategorii + podkategorie
 $catIds = function_exists('cc_get_category_descendant_ids') ? cc_get_category_descendant_ids($catId) : [$catId];
 if (count($catIds) === 0) { $catIds = [$catId]; }
 $catIdsSql = implode(',', array_map('intval', $catIds));
// pobieramy produkty z tej kategorii
 $q = mysqli_query(
 $link,
 "SELECT id, title, description, price_netto, vat, stock_qty, status, expires_at, image_url, sound_file, spec_1, spec_2, spec_3, spec_4
 FROM products
 WHERE category_id IN ($catIdsSql)
 ORDER BY created_at DESC, id DESC"
 );

 if (!$q) {
 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd SQL:</b> '. cc_h(mysqli_error($link))
. '</div>';
 }

 $cards = '';
 $count = 0;
 while ($p = mysqli_fetch_assoc($q)) {
 $count++;
 $cards.= cc_render_product_card($p);
 }

 if ($count === 0) {
 $cards = '<div class="card" style="background: rgba(255,255,255,.05); border-color: rgba(245,158,11,.35);">'
. '<b>Brak produktów w tej kategorii.</b><br />'
. 'Zaimportuj plik <code>lab11_products_template.sql</code> albo dodaj produkty w panelu admina.'
. '</div>';
 } else {
 $cards = '<div class="productGrid">'. $cards. '</div>';
 }

 return '<section class="card">'
. '<h2>Produkty</h2>'
. '<p>: produkty są w bazie (netto + VAT), a cena brutto jest liczona w PHP.</p>'
. $cards
. '</section>';
}


// ================================
// Best-sellery (dynamiczne) — 4 produkty z najmniejszym stanem magazynowym
// ================================
function cc_get_low_stock_products(int $limit = 4): array {
  global $link;
  $limit = max(1, (int)$limit);

  $sql = "SELECT id, title, description, price_netto, vat, stock_qty, status, expires_at,
                 size, image_url, sound_file, spec_1, spec_2, spec_3, spec_4, category_id
          FROM products
          WHERE status = 1
          ORDER BY stock_qty ASC, id ASC
          LIMIT $limit";

  $q = mysqli_query($link, $sql);
  if (!$q) return [];
  $rows = [];
  while ($row = mysqli_fetch_assoc($q)) {
    $rows[] = $row;
  }
  return $rows;
}

function PokazBestSelleryLowStock(int $limit = 4): string {
  $items = cc_get_low_stock_products($limit);

  $cards = '';
  foreach ($items as $p) {
    // Uzywamy tej samej karty co w katalogu (z przyciskiem "Dodaj do koszyka")
    $cards .= cc_render_product_card($p);
  }

  if ($cards === '') {
    $cards = '<div class="card" style="background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.12);">'
           . 'Brak aktywnych produktów w bazie.'
           . '</div>';
  }

  return '<section class="card" style="margin-top:14px;">'
       . '<h3>Best-sellery</h3>'
       . '<p>4 produkty z najmniejszym stanem magazynowym.</p>'
       . '<div class="productGrid">' . $cards . '</div>'
       . '</section>';
}
