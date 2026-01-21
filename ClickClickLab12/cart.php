<?php
// ==========================================
// ClickClick — cart.php (Lab12)
// Ten plik robi koszyk sklepu.
// Wymaganie z Lab12: koszyk ma byc oparty na sesji PHP.
// ==========================================

// Dolaczamy helpery (wspolne funkcje)
require_once __DIR__ . '/helpers.php';

// -----------------------------
// Funkcje koszyka (SESJA)
// -----------------------------

// Start/ustawienie koszyka w sesji
function cc_cart_init(): void {
  if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // product_id => qty
  }
}

// Ile sztuk jest w koszyku (suma ilosci)
function cc_cart_count(): int {
  cc_cart_init();
  $sum = 0;
  foreach ($_SESSION['cart'] as $pid => $qty) {
    $sum += (int)$qty;
  }
  return $sum;
}

// Dodanie produktu do koszyka
function cc_cart_add(int $pid, int $qty = 1): void {
  cc_cart_init();
  if ($pid <= 0) return;
  if ($qty <= 0) $qty = 1;

  if (!isset($_SESSION['cart'][$pid])) {
    $_SESSION['cart'][$pid] = 0;
  }
  $_SESSION['cart'][$pid] += $qty;
}

// Zmiana ilosci produktu
function cc_cart_update(int $pid, int $qty): void {
  cc_cart_init();
  if ($pid <= 0) return;

  if ($qty <= 0) {
    unset($_SESSION['cart'][$pid]);
  } else {
    $_SESSION['cart'][$pid] = $qty;
  }
}

// Usuniecie produktu z koszyka
function cc_cart_remove(int $pid): void {
  cc_cart_init();
  if ($pid <= 0) return;
  unset($_SESSION['cart'][$pid]);
}

// Wyczyszczenie koszyka
function cc_cart_clear(): void {
  $_SESSION['cart'] = [];
}

// -----------------------------
// Widok koszyka (HTML)
// -----------------------------

function PokazKoszyk(): string {
  global $link;

  cc_cart_init();

  // Obsluga akcji (add/remove/update/clear)
  $action = isset($_GET['action']) ? $_GET['action'] : '';
  $action = preg_replace('/[^a-zA-Z0-9_\-]/', '', $action);

  // Dodanie do koszyka (przychodzi z formularza POST)
  if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
    cc_cart_add($pid, $qty);

    return '<section class="card">'
      . '<h2>Koszyk</h2>'
      . '<p>Produkt został dodany do koszyka ✅</p>'
      . '<div class="cartActions">'
        . '<a class="btn" href="index.php?idp=cart">Przejdź do koszyka</a>'
        . '<a class="btn secondary" href="index.php?idp=' . cc_h(isset($_GET['back']) ? $_GET['back'] : 'switches') . '">Wróć</a>'
      . '</div>'
      . '</section>'
      . cc_cart_render_table();
  }

  // Usuwanie
  if ($action === 'remove') {
    $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
    cc_cart_remove($pid);
  }

  // Czyszczenie
  if ($action === 'clear') {
    cc_cart_clear();
  }

  // Aktualizacja ilosci (POST)
  if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
      foreach ($_POST['qty'] as $pidStr => $qtyStr) {
        $pid = (int)$pidStr;
        $qty = (int)$qtyStr;
        cc_cart_update($pid, $qty);
      }
    }
  }

  // Standardowy widok koszyka
  return '<section class="card">'
    . '<h2>Koszyk</h2>'
    . '<p>Lab12: koszyk jest oparty na sesji (SESSION). Możesz dodać produkty, zmienić ilość i zobaczyć podsumowanie.</p>'
    . cc_cart_render_table()
    . '</section>';
}

// Render tabeli koszyka + podsumowania
function cc_cart_render_table(): string {
  global $link;

  cc_cart_init();

  if (empty($_SESSION['cart'])) {
    return '<div class="cartEmpty">Twój koszyk jest pusty. Dodaj coś ze sklepu 🙂</div>';
  }

  // Pobieramy produkty z bazy po ID z koszyka
  $ids = array_keys($_SESSION['cart']);
  $ids = array_map('intval', $ids);
  $ids = array_filter($ids, fn($x) => $x > 0);

  if (empty($ids)) {
    return '<div class="cartEmpty">Koszyk jest pusty.</div>';
  }

  $idList = implode(',', $ids);

  $q = mysqli_query(
    $link,
    // LIMIT dodajemy dla "bezpieczenstwa" (zeby nie polecialo milion rekordow)
    "SELECT id, title, price_netto, vat, stock_qty, status FROM products WHERE id IN ($idList) LIMIT 200"
  );

  if (!$q) {
    return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
      . '<b>Błąd SQL:</b> ' . cc_h(mysqli_error($link))
      . '</div>';
  }

  $products = [];
  while ($row = mysqli_fetch_assoc($q)) {
    $products[(int)$row['id']] = $row;
  }

  $rows = '';
  $sumTotal = 0.0;

  foreach ($_SESSION['cart'] as $pid => $qty) {
    $pid = (int)$pid;
    $qty = (int)$qty;
    if ($qty <= 0) continue;

    if (!isset($products[$pid])) {
      // produkt usuniety z bazy - pokazujemy linie ostrzegawcza
      $rows .= '<tr>'
        . '<td colspan="5" class="cartWarn">Produkt ID ' . (int)$pid . ' nie istnieje w bazie (usuniety?)</td>'
        . '</tr>';
      continue;
    }

    $p = $products[$pid];
    $title = cc_h($p['title']);
    $netto = (float)$p['price_netto'];
    $vat = (int)$p['vat'];
    $brutto = cc_price_brutto($netto, $vat);
    $line = $brutto * $qty;
    $sumTotal += $line;

    $rows .= '<tr>'
      . '<td class="cartColName">' . $title . '</td>'
      . '<td class="cartColPrice">' . number_format($brutto, 2, ',', ' ') . ' zł</td>'
      . '<td class="cartColQty">'
        . '<input class="cartQty" type="number" min="0" name="qty[' . (int)$pid . ']" value="' . (int)$qty . '" />'
      . '</td>'
      . '<td class="cartColLine">' . number_format($line, 2, ',', ' ') . ' zł</td>'
      . '<td class="cartColAct"><a class="linkDanger" href="index.php?idp=cart&action=remove&pid=' . (int)$pid . '" onclick="return confirm(\"Usunąć produkt z koszyka?\");">Usuń</a></td>'
    . '</tr>';
  }

  $summary = '<div class="cartSummary">'
    . '<div><b>Suma:</b> ' . number_format($sumTotal, 2, ',', ' ') . ' zł</div>'
    . '<div class="cartSummaryHint">Cena brutto liczona dynamicznie (netto + VAT).</div>'
    . '</div>';

  return '<form method="post" action="index.php?idp=cart&action=update">'
    . '<div class="cartTableWrap">'
      . '<table class="cartTable">'
        . '<thead>'
          . '<tr>'
            . '<th>Produkt</th>'
            . '<th>Cena brutto</th>'
            . '<th>Ilość</th>'
            . '<th>Razem</th>'
            . '<th></th>'
          . '</tr>'
        . '</thead>'
        . '<tbody>' . $rows . '</tbody>'
      . '</table>'
    . '</div>'
    . $summary
    . '<div class="cartActions">'
      . '<button class="btn" type="submit">Aktualizuj ilości</button>'
      . '<a class="btn secondary" href="index.php?idp=cart&action=clear" onclick="return confirm(\"Wyczyścić cały koszyk?\");">Wyczyść koszyk</a>'
    . '</div>'
  . '</form>';
}
