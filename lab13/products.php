<?php
/**
 * ClickClick — products.php 
 * Moduł wyświetlania produktów.
 * Odpowiada za pobieranie ofert z bazy danych, filtrowanie i renderowanie widoku (kart produktów).
 */

require_once __DIR__. '/helpers.php';

/**
 * Sprawdza dostępność produktu.
 * Weryfikuje status, stan magazynowy oraz datę wygaśnięcia.
 *
 * @param array $p Tablica asocjacyjna produktu z bazy danych
 * @return bool True jeśli produkt jest dostępny do zakupu
 */
function cc_is_available(array $p): bool {
    // 1. Status aktywności (1 = aktywny)
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

 $stmt = mysqli_prepare($link, "SELECT id FROM categories WHERE name=? LIMIT 1");
 mysqli_stmt_bind_param($stmt, "s", $name);
 mysqli_stmt_execute($stmt);
 $res = mysqli_stmt_get_result($stmt);
 
 if ($res && $row = mysqli_fetch_assoc($res)) {
     return (int)$row['id'];
 }
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


// Render jednej karty produktu (wyglad sklepu)


/**
 * Wyświetla stronę ze szczegółami produktu.
 * Pokazuje zdjęcie, opis, cenę, specyfikację i przycisk dodawania do koszyka.
 *
 * @param int $id ID produktu
 * @return string HTML ze szczegółami produktu
 */
function PokazProdukt(int $id): string {
  global $link;
  $id = (int)$id;
  if ($id <= 0) return '<div class="card">Nieprawidłowe ID produktu.</div>';

  $stmt = mysqli_prepare($link, "SELECT * FROM products WHERE id=? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $q = mysqli_stmt_get_result($stmt);

  if (!$q || mysqli_num_rows($q) === 0) {
      return '<div class="card">Produkt nie istnieje.</div>';
  }
  $p = mysqli_fetch_assoc($q);

  $title = cc_h($p['title']);
  $desc = cc_h($p['description']);
  $img = cc_h($p['image_url']);
  
  $netto = (float)$p['price_netto'];
  $vat = (int)$p['vat'];
  $brutto = cc_price_brutto($netto, $vat);

  $available = cc_is_available($p);
  $badge = $available 
     ? '<span class="badge"><span class="dot green"></span> Dostępny ('.(int)$p['stock_qty'].' szt.)</span>' 
     : '<span class="badge"><span class="dot"></span> Niedostępny</span>';

  // Dzwiek
  $soundBtn = '';
  if (!empty($p['sound_file'])) {
      $sound = cc_h($p['sound_file']);
      $soundBtn = '<div style="margin: 20px 0;">
          <button class="speakerBtn" type="button" title="Odsłuch" data-sound="'.$sound.'" data-playing="0">🔊</button>
      </div>';
  }
  
  // Obrazek
  $imgBlock = $img !== ''
     ? '<img src="'.$img.'" alt="'.$title.'" style="max-width:100%; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.3);" />'
     : '<div style="background:rgba(255,255,255,0.05); height:300px; display:flex; align-items:center; justify-content:center; border-radius:12px;">Brak zdjęcia</div>';

  // Koszyk
  $pid = (int)$p['id'];
  $cartForm = '';
  if ($available) {
      $cartForm = '<form class="cartForm" method="post" action="index.php?idp=cart&action=add" style="margin-top:20px;">'
      . '<input type="hidden" name="pid" value="'.$pid.'" />'
      . '<div style="display:flex; gap:10px; align-items:center;">'
      . '<input type="number" name="qty" value="1" min="1" max="'.(int)$p['stock_qty'].'" class="input" style="width:80px; text-align:center;" />'
      . '<button class="cartBtn" type="submit">Dodaj do koszyka</button>'
      . '</div>'
      . '</form>';
  } else {
      $cartForm = '<div style="margin-top:20px;"><button class="cartBtn" disabled>Produkt niedostępny</button></div>';
  }

  // Specyfikacja
  $specs = '';
  for($i=1; $i<=4; $i++) {
      if(!empty($p['spec_'.$i])) {
          $specs .= '<div class="spec">'.cc_h($p['spec_'.$i]).'</div>';
      }
  }
  if($specs) $specs = '<div class="specGrid" style="margin:20px 0;">'.$specs.'</div>';

  return '
  <div class="grid2" style="grid-template-columns: 1fr 1fr; align-items:start;">
     <div style="padding:10px;">
        '.$imgBlock.'
     </div>
     <div class="card">
        <h1>'.$title.'</h1>
        <div style="margin-bottom:10px;">'.$badge.'</div>
        <div class="priceRow">
           <div class="price" style="font-size:32px;">'.number_format($brutto, 2, ',', ' ').' zł</div>
        </div>
        <small style="opacity:0.7;">Cena netto: '.number_format($netto, 2, ',', ' ').' zł (VAT '.$vat.'%)</small>
        
        <div style="margin-top:20px; line-height:1.6; font-size:16px;">
           '.nl2br($desc).'
        </div>
        
        '.$specs.'
        '.$soundBtn.'
        '.$cartForm.'
        
        <div style="margin-top:30px; border-top:1px solid rgba(255,255,255,0.1); padding-top:10px;">
           <small>Kategoria ID: '.(int)$p['category_id'].' | Dodano: '.$p['created_at'].'</small><br>
           <a href="index.php?idp=kategorie" class="smallBtn" style="margin-top:10px; display:inline-block;">&larr; Wróć do sklepu</a>
        </div>
     </div>
  </div>
  ';
}

function cc_render_product_card(array $p): string {
 $title = cc_h($p['title'] ?? '');
 $desc = cc_h($p['description'] ?? '');
 $img = cc_h($p['image_url'] ?? '');
 $pid = (int)($p['id'] ?? 0);
 $linkDetail = "index.php?idp=product&id=$pid";

 $netto = (float)$p['price_netto'];
 $vat = (int)$p['vat'];
 $brutto = cc_price_brutto($netto, $vat);

 $available = cc_is_available($p);
 $badge = $available
 ? '<span class="badge"><span class="dot green"></span> Dostępny</span>'
 : '<span class="badge"><span class="dot"></span> Niedostępny</span>';

 $soundBtn = '';
 if (!empty($p['sound_file'])) {
  $sound = cc_h($p['sound_file']);
  $soundBtn = '<button class="speakerBtn" type="button" title="Odsłuch" data-sound="'. $sound. '" data-playing="0">🔊</button>';
 }

 $spec1 = cc_h($p['spec_1'] ?? '');
 // ... simple specs hidden from card potentially or shown
 $specHtml = ''; // (Możemy uprościć kartę, jeśli są szczegóły, ale zostawiamy jak było)
 // Kod specyfikacji pomijam w tym diffie (zostaje stary, tylko linki zmieniam)
 // A czekaj, musze dac cala funkcje zeby replace zadzialal poprawnie
 
 $spec1 = cc_h($p['spec_1'] ?? '');
 $spec2 = cc_h($p['spec_2'] ?? '');
 $spec3 = cc_h($p['spec_3'] ?? '');
 $spec4 = cc_h($p['spec_4'] ?? '');

 $specHtml = '';
 $specPairs = [$spec1, $spec2, $spec3, $spec4];
 $specPairs = array_filter($specPairs, fn($x) => $x !== '');
 if (!empty($specPairs)) {
  $specHtml.= '<div class="specGrid">';
  foreach ($specPairs as $s) $specHtml.= '<div class="spec">'. $s. '</div>';
  $specHtml.= '</div>';
 }

 $imgBlock = $img !== ''
 ? '<div class="productImg"><a href="'.$linkDetail.'"><img src="'. $img. '" alt="'. $title. '" /></a></div>'
 : '<div class="productImg" style="display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.6);"><a href="'.$linkDetail.'" style="color:inherit;text-decoration:none;">Brak zdjęcia</a></div>';

 $cartForm = '';
 if ($pid > 0) {
 if ($available) {
 $cartForm = '<form class="cartForm" method="post" action="index.php?idp=cart&action=add">'
. '<input type="hidden" name="pid" value="'. $pid. '" />'
. '<input type="hidden" name="qty" value="1" />'
. '<button class="cartBtn" type="submit">Dodaj do koszyka</button>'
. '</form>';
 } else {
 $cartForm = '<div class="cartForm"><button class="cartBtn" type="button" disabled>Brak na stanie</button></div>';
 }
 }

 return '<article class="productCard cardHover">'
. '<div class="productHeader">'
. '<div>'
. '<h3><a href="'.$linkDetail.'" style="color:inherit; text-decoration:none;">'. $title. '</a></h3>'
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
. '<span class="smallBtn">netto: '. number_format($netto, 2, ',', ' '). ' zł</span>'
. '</div>'
. '<div class="tagRow">'
. '<a href="'.$linkDetail.'" class="tag">Pokaż szczegóły</a>'
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

/**
 * Wyświetla stronę "Wszystkie produkty" z obsługą filtrów i wyszukiwarki.
 * Pobiera parametry GET (search, min_price, max_price, category) i buduje odpowiednie zapytanie SQL.
 *
 * @return string HTML z układem strony (sidebar + grid produktów)
 */
function PokazWszystkieProdukty(): string {
  global $link;
  
  // 1. Parametry filtrowania
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
  $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
  $onlyAvailable = isset($_GET['only_available']) ? (int)$_GET['only_available'] : 0;
  $filterCats = isset($_GET['f_cat']) && is_array($_GET['f_cat']) ? array_map('intval', $_GET['f_cat']) : [];
  
  // 2. Budowanie zapytania SQL
  $where = ["status = 1"];
  
  if ($search !== '') {
      $safeSearch = mysqli_real_escape_string($link, $search);
      $where[] = "(title LIKE '%$safeSearch%' OR description LIKE '%$safeSearch%')";
  }
  
  if ($minPrice !== null) {
      $where[] = "price_netto * (1 + vat/100) >= $minPrice"; 
  }
  if ($maxPrice !== null) {
      $where[] = "price_netto * (1 + vat/100) <= $maxPrice";
  }
  if ($onlyAvailable) {
      $where[] = "stock_qty > 0";
  }
  if (!empty($filterCats)) {
      $catsList = implode(',', $filterCats);
      $where[] = "category_id IN ($catsList)";
  }
  
  $whereSql = implode(' AND ', $where);
  $sql = "SELECT * FROM products WHERE $whereSql ORDER BY id DESC";
  
  $q = mysqli_query($link, $sql);
  
  // Renderowanie kart produktow
  $cards = '';
  $count = 0;
  if ($q) {
      while ($p = mysqli_fetch_assoc($q)) {
          $cards .= cc_render_product_card($p);
          $count++;
      }
  }
  
  if ($count === 0) {
      $cards = '<div class="card" style="grid-column: 1/-1;">Brak produktów spełniających kryteria.</div>';
  } else {
      $cards = '<div class="productGrid" style="grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));">'.$cards.'</div>';
  }
  
  // 3. Renderowanie paska bocznego (Filtry)
  $catsHtml = '';
  $resCat = mysqli_query($link, "SELECT id, name FROM categories WHERE parent_id=0 ORDER BY name ASC"); 
  if ($resCat) {
      while ($c = mysqli_fetch_assoc($resCat)) {
          $checked = in_array($c['id'], $filterCats) ? 'checked' : '';
          $catsHtml .= '<label style="display:block; margin-bottom:5px; cursor:pointer;">
              <input type="checkbox" name="f_cat[]" value="'.$c['id'].'" '.$checked.'> '.cc_h($c['name']).'
          </label>';
          
          $subQ = mysqli_query($link, "SELECT id, name FROM categories WHERE parent_id=".(int)$c['id']);
          while($sub = mysqli_fetch_assoc($subQ)){
               $subChecked = in_array($sub['id'], $filterCats) ? 'checked' : '';
               $catsHtml .= '<label style="display:block; margin-bottom:5px; margin-left:20px; font-size:0.9em; opacity:0.8; cursor:pointer;">
                  <input type="checkbox" name="f_cat[]" value="'.$sub['id'].'" '.$subChecked.'> '.cc_h($sub['name']).'
               </label>';
          }
      }
  }
  
  $checkedAvail = $onlyAvailable ? 'checked' : '';
  $valMin = $minPrice !== null ? $minPrice : '';
  $valMax = $maxPrice !== null ? $maxPrice : '';
  
  $sidebar = '
  <aside class="filterSidebar card" style="height:fit-content;">
      <h3>Filtry</h3>
      <form method="get" action="index.php">
          <input type="hidden" name="idp" value="all_products">
          
          <div style="margin-bottom:15px;">
              <label><b>Szukaj</b></label>
              <input type="text" name="search" value="'.cc_h($search).'" class="input" placeholder="Nazwa produktu..." style="width:100%; margin-top:5px;">
          </div>

          <div style="margin-bottom:15px;">
              <label><b>Cena (zł)</b></label>
              <div style="display:flex; gap:5px; margin-top:5px;">
                  <input type="number" name="min_price" value="'.$valMin.'" placeholder="Od" class="input" style="width:100%">
                  <input type="number" name="max_price" value="'.$valMax.'" placeholder="Do" class="input" style="width:100%">
              </div>
          </div>
          
          <div style="margin-bottom:15px;">
               <label style="cursor:pointer;">
                   <input type="checkbox" name="only_available" value="1" '.$checkedAvail.'> <b>Tylko dostępne</b>
               </label>
          </div>
          
          <div style="margin-bottom:15px;">
              <label><b>Kategorie</b></label>
              <div style="max-height:300px; overflow-y:auto; margin-top:5px; border:1px solid rgba(255,255,255,0.1); padding:10px; border-radius:6px;">
                  '.$catsHtml.'
              </div>
          </div>
          
          <button type="submit" class="btn" style="width:100%;">Filtruj</button>
          <a href="index.php?idp=all_products" class="smallBtn" style="display:block; text-align:center; margin-top:10px;">Wyczyść filtry</a>
      </form>
  </aside>';

  return '
  <style>
    .store-layout { display: grid; grid-template-columns: 250px 1fr; gap: 20px; }
    @media (max-width: 768px) { .store-layout { grid-template-columns: 1fr; } }
  </style>
  <div class="store-layout">
      '.$sidebar.'
      <section>
          <div class="card">
              <h2>Wszystkie produkty</h2>
              <p>Przeglądaj pełną ofertę sklepu.</p>
          </div>
          <div style="margin-top:20px;">
              '.$cards.'
          </div>
      </section>
  </div>
  ';
}
