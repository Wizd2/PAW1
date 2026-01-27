<?php
// ================================
// ClickClick — sklep.php 
// Ten plik robi kategorie sklepu.
// W chodzi o to, zeby:
// - miec tabele kategorii w bazie danych
// - wyswietlic je w formie drzewa (rekurencja)
//
// Uwaga: polaczenie do bazy jest w cfg.php (zmienna $link)
// ================================

// Dolaczamy helpery (require_once, zeby nie bylo "Cannot redeclare")
require_once __DIR__. '/helpers.php';

// Pobieramy jedna kategorie po ID (przydaje sie jak user kliknie kategorie)
function cc_get_category_by_id(int $id): ?array {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return null;
 }

 $q = mysqli_query($link, "SELECT id, parent_id, name FROM categories WHERE id=$id LIMIT 1");
 if (!$q) {
 return null;
 }

 $row = mysqli_fetch_assoc($q);
 return $row ?: null;
}

// Rekurencyjne wyswietlanie drzewa kategorii
// parent_id = 0 oznacza "kategorie glowne"
function cc_render_category_tree(int $parent_id = 0, int $level = 0): string {
 global $link;

 $parent_id = (int)$parent_id;
 $level = (int)$level;

 // Pobieramy dzieci (podkategorie) dla danego parent_id
 $q = mysqli_query(
 $link,
 "SELECT id, parent_id, name FROM categories WHERE parent_id=$parent_id ORDER BY name ASC"
 );

 if (!$q) {
 // Jak cos poszlo nie tak, to pokazujemy blad (na labach to pomaga)
 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd SQL:</b> '. cc_h(mysqli_error($link))
. '</div>';
 }

 $items = '';
 while ($row = mysqli_fetch_assoc($q)) {
 $id = (int)$row['id'];
 $name = cc_h($row['name']);

 // Link do wybranej kategorii (cat=ID)
 $items.= '<li style="margin: 6px 0;">'
. '<a href="index.php?idp=kategorie&cat='. $id. '"><b>'. $name. '</b></a>';

 // Rekurencja: probujemy wyrenderowac dzieci
 $childHtml = cc_render_category_tree($id, $level + 1);

 // Zeby nie robic pustych <ul>, sprawdzamy czy w srodku jest jakis <li>
 if (strpos($childHtml, '<li') !== false) {
 $items.= '<ul class="ul" style="margin-top:6px; margin-left: '. (14 + $level * 10). 'px;">'
. $childHtml
. '</ul>';
 }

 $items.= '</li>';
 }

 // Zwracamy same <li> (a <ul> dodamy wyzej, gdzie trzeba)
 return $items;
}

// Glowna funkcja: pokazuje strone "Kategorie"
function PokazKategorieSklepu(): string {
  $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
  if ($catId > 0) {
    $cat = cc_get_category_by_id($catId);
    $name = $cat ? cc_h($cat['name']) : 'Kategoria';
    $out = '<section class="card"><h2>'. $name .'</h2>'
         . '<p>Produkty z wybranej kategorii oraz jej podkategorii.</p></section>';

    if (function_exists('PokazProduktySklepu')) {
      $out .= PokazProduktySklepu();
    }
    return $out;
  }

  $tree = cc_render_category_tree(0, 0);
  if (strpos($tree, '<li') === false) {
    return '<section class="card"><h2>Wszystkie kategorie</h2>'
         . '<p>Brak kategorii w bazie. Dodaj je w CMS albo zaimportuj SQL.</p></section>';
  }

  return '<section class="card">'
       . '<h2>Wszystkie kategorie</h2>'
       . '<p>Kliknij kategorię, aby zobaczyć produkty.</p>'
       . '<ul class="ul">'. $tree .'</ul>'
       . '</section>';
}

// =========================
// Filmy / FAQ / Poradnik — frontend
// ================================
function PokazFilmy(): string {
  global $link;
  $html = '<section class="card"><h2>Filmy</h2><p>Materiały wideo z YouTube.</p></section>';
  $res = mysqli_query($link, "SELECT title,youtube_url FROM videos WHERE status=1 ORDER BY id DESC");
  if (!$res) return $html . '<div class="card">Brak tabeli videos (zaimportuj SQL).</div>';
  $items = [];
  while ($row = mysqli_fetch_assoc($res)) $items[] = $row;
  if (!$items) return $html . '<div class="card">Brak filmów.</div>';
  $html .= '<div class="grid2">';
  foreach ($items as $it) {
    $t = _cc_escape($it['title']);
    $u = _cc_escape($it['youtube_url']);
    // simple embed: convert watch?v= to embed/
    $embed = preg_replace('~watch\?v=~', 'embed/', $u);
    $html .= '<div class="card"><h3>'.$t.'</h3>
      <div style="position:relative; padding-top:56.25%; border-radius:14px; overflow:hidden;">
        <iframe src="'.$embed.'" title="'.$t.'" style="position:absolute; inset:0; width:100%; height:100%; border:0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>
      <p style="margin-top:10px;"><a class="smallBtn" href="'.$u.'" target="_blank" rel="noopener">Otwórz na YouTube</a></p>
    </div>';
  }
  $html .= '</div>';
  return $html;
}

function PokazFAQ(): string {
  global $link;
  $html = '<section class="card"><h2>FAQ</h2><p>Najczęściej zadawane pytania.</p></section>';
  $res = mysqli_query($link, "SELECT question,answer FROM faq WHERE status=1 ORDER BY sort_order ASC, id ASC");
  if (!$res) return $html . '<div class="card">Brak tabeli faq (zaimportuj SQL).</div>';
  $rows = [];
  while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
  if (!$rows) return $html . '<div class="card">Brak wpisów FAQ.</div>';
  $html .= '<div class="card"><div class="faqList">';
  foreach ($rows as $r) {
    $q = _cc_escape($r['question']);
    $a = nl2br(_cc_escape($r['answer']));
    $html .= '<details style="margin:10px 0;"><summary><b>'.$q.'</b></summary><div style="margin-top:8px; opacity:.95;">'.$a.'</div></details>';
  }
  $html .= '</div></div>';
  return $html;
}

function PokazPoradnik(): string {
  global $link;
  $html = '<section class="card"><h2>Poradnik</h2><p>Krótkie artykuły i porady.</p></section>';
  $res = mysqli_query($link, "SELECT id,title,body,created_at FROM guides WHERE status=1 ORDER BY id DESC");
  if (!$res) return $html . '<div class="card">Brak tabeli guides (zaimportuj SQL).</div>';
  $rows = [];
  while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;
  if (!$rows) return $html . '<div class="card">Brak artykułów.</div>';
  foreach ($rows as $r) {
    $t = _cc_escape($r['title']);
    $b = nl2br(_cc_escape($r['body']));
    $date = _cc_escape($r['created_at'] ?? '');
    $html .= '<article class="card" style="margin-top:14px;"><h3>'.$t.'</h3><div style="opacity:.75; font-size:12px; margin-bottom:8px;">'.$date.'</div><div>'.$b.'</div></article>';
  }
  return $html;
}