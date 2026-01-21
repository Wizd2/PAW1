<?php
// ================================
// ClickClick — sklep.php (Lab10)
// Ten plik robi kategorie sklepu.
// W Lab10 chodzi o to, zeby:
// - miec tabele kategorii w bazie danych
// - wyswietlic je w formie drzewa (rekurencja)
//
// Uwaga: polaczenie do bazy jest w cfg.php (zmienna $link)
// ================================

// Dolaczamy helpery (require_once, zeby nie bylo "Cannot redeclare")
require_once __DIR__ . '/helpers.php';

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
      . '<b>Błąd SQL:</b> ' . cc_h(mysqli_error($link))
      . '</div>';
  }

  $items = '';
  while ($row = mysqli_fetch_assoc($q)) {
    $id = (int)$row['id'];
    $name = cc_h($row['name']);

    // Link do wybranej kategorii (cat=ID)
    $items .= '<li style="margin: 6px 0;">'
      . '<a href="index.php?idp=kategorie&cat=' . $id . '"><b>' . $name . '</b></a>';

    // Rekurencja: probujemy wyrenderowac dzieci
    $childHtml = cc_render_category_tree($id, $level + 1);

    // Zeby nie robic pustych <ul>, sprawdzamy czy w srodku jest jakis <li>
    if (strpos($childHtml, '<li') !== false) {
      $items .= '<ul class="ul" style="margin-top:6px; margin-left: ' . (14 + $level * 10) . 'px;">'
        . $childHtml
        . '</ul>';
    }

    $items .= '</li>';
  }

  // Zwracamy same <li> (a <ul> dodamy wyzej, gdzie trzeba)
  return $items;
}

// Glowna funkcja: pokazuje strone "Kategorie"
function PokazKategorieSklepu(): string {
  $catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
  $selected = $catId > 0 ? cc_get_category_by_id($catId) : null;

  $title = 'Kategorie sklepu';
  $subtitle = 'W Lab10 robimy drzewo kategorii (rekurencja).';

  if ($selected) {
    $title = 'Kategoria: ' . cc_h($selected['name']);
    $subtitle = 'Poniżej nadal masz całe drzewo, a wybrana kategoria jest tylko informacją.';
  }

  $tree = cc_render_category_tree(0, 0);

  // Jak nie ma zadnej kategorii w bazie, to tree bedzie puste
  $treeHtml = (strpos($tree, '<li') !== false)
    ? '<ul class="ul" style="margin-top:10px;">' . $tree . '</ul>'
    : '<div class="card" style="background: rgba(255,255,255,.05); border-color: rgba(245,158,11,.35);">'
        . '<b>Brak kategorii w bazie.</b><br />'
        . 'Zaimportuj plik <code>lab10_categories_template.sql</code> w phpMyAdmin.'
        . '</div>';

  // Prosty opis + "help" co trzeba zrobic
  return '<section class="card">'
    . '<h2>' . $title . '</h2>'
    . '<p>' . $subtitle . '</p>'

    . '<div class="badges">'
    .   '<div class="badge"><span class="dot"></span> Tabela: <b>categories</b></div>'
    .   '<div class="badge"><span class="dot green"></span> parent_id = podkategorie</div>'
    .   '<div class="badge"><span class="dot"></span> Rekurencja w PHP</div>'
    . '</div>'

    . '<h3 style="margin-top:16px;">Drzewo kategorii</h3>'
    . $treeHtml

    . '<p style="margin-top:14px;">
        <b>Tip:</b> kategoriami zarządzasz w panelu admina: <code>/admin/admin.php</code> → zakładka <b>Kategorie</b>.
      </p>'
    . '</section>';
}
