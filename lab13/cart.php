<?php
/**
 * ClickClick — cart.php
 * Logic i widok koszyka zakupowego.
 * Zarządza sesją koszyka ($_SESSION['cart']) oraz synchronizacją z bazą danych dla zalogowanych użytkowników.
 */

require_once __DIR__ . '/helpers.php';

// =============================
// Logika Koszyka (Backend)
// =============================

/**
 * Inicjalizuje tablicę koszyka w sesji, jeśli nie istnieje.
 */
function cc_cart_init(): void {
  if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // format: [product_id => quantity]
  }
}

/**
 * Zwraca łączną liczbę produktów w koszyku (do licznika w nagłówku).
 */
function cc_cart_count(): int {
  cc_cart_init();
  $sum = 0;
  foreach ($_SESSION['cart'] as $pid => $qty) {
    $sum += (int)$qty;
  }
  return $sum;
}

// Helper: synchronizacja pojedynczego produktu z baza
function cc_cart_db_sync(int $pid, int $qty): void {
    global $link;
    if (!isset($_SESSION['user_id'])) return;
    $uid = (int)$_SESSION['user_id'];
    
    // Sprawdzamy czy rekord istnieje
    $q = mysqli_query($link, "SELECT id FROM cart_items WHERE user_id=$uid AND product_id=$pid LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        if ($qty > 0) {
            mysqli_query($link, "UPDATE cart_items SET quantity=$qty WHERE user_id=$uid AND product_id=$pid");
        } else {
            mysqli_query($link, "DELETE FROM cart_items WHERE user_id=$uid AND product_id=$pid");
        }
    } else {
        if ($qty > 0) {
            mysqli_query($link, "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($uid, $pid, $qty)");
        }
    }
}

// Helper: zaladowanie koszyka z bazy (przy logowaniu)
function cc_cart_db_load(int $uid): void {
    global $link;
    cc_cart_init();
    $q = mysqli_query($link, "SELECT product_id, quantity FROM cart_items WHERE user_id=$uid");
    while ($row = mysqli_fetch_assoc($q)) {
        $pid = (int)$row['product_id'];
        $qty = (int)$row['quantity'];
        // Jesli produkt jest w sesji, mozna sumowac lub nadpisac. Sumujemy:
        if (isset($_SESSION['cart'][$pid])) {
             $_SESSION['cart'][$pid] += $qty;
             // Update DB with new sum
             cc_cart_db_sync($pid, $_SESSION['cart'][$pid]);
        } else {
             $_SESSION['cart'][$pid] = $qty;
        }
    }
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
  
  // Sync DB
  cc_cart_db_sync($pid, $_SESSION['cart'][$pid]);
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
  
  // Sync DB
  cc_cart_db_sync($pid, $qty);
}

// Usuniecie produktu z koszyka
function cc_cart_remove(int $pid): void {
  cc_cart_init();
  if ($pid <= 0) return;
  unset($_SESSION['cart'][$pid]);
  
  // Sync DB
  cc_cart_db_sync($pid, 0);
}

// Wyczyszczenie koszyka
function cc_cart_clear(): void {
  // Jesli user jest zalogowany, czyscimy tez w bazie (tylko przy zlozonym zamowieniu? uzytkownik chce "reset"? 
  // zazwyczaj clear() jest po zamowieniu. Ale jesli to jest "obnuzam koszyk", to tez z bazy.
  global $link;
  if (isset($_SESSION['user_id'])) {
      $uid = (int)$_SESSION['user_id'];
      mysqli_query($link, "DELETE FROM cart_items WHERE user_id=$uid");
  }
  $_SESSION['cart'] = [];
}

// Zlozenie zamowienia (zapis do DB)
function cc_cart_checkout(): string {
    global $link;
    cc_cart_init();

    if (empty($_SESSION['cart'])) {
        return '<div class="card">Koszyk jest pusty.</div>';
    }

    // Sprawdzenie logowania
    if (!isset($_SESSION['user_id'])) {
        // "Jesli niezalogowany nic sie nie dzieje" - tutaj w backendzie tez blokujemy
        return ''; 
    }

    $userId = (int)$_SESSION['user_id'];
    $ids = array_keys($_SESSION['cart']);
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, fn($x) => $x > 0);
    $idList = implode(',', $ids);

    if (empty($idList)) {
        return '<div class="card">Błąd koszyka (brak produktów).</div>';
    }

    // Pobieramy produkty zeby miec ceny
    $q = mysqli_query($link, "SELECT id, price_netto, vat FROM products WHERE id IN ($idList)");
    if (!$q) return '<div class="card">Błąd bazy danych.</div>';

    $products = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $products[(int)$row['id']] = $row;
    }

    $totalAmount = 0.0;
    $orderItems = [];

    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($qty <= 0 || !isset($products[$pid])) continue;

        $p = $products[$pid];
        $priceNetto = (float)$p['price_netto'];
        $vat = (int)$p['vat'];
        $priceGross = $priceNetto * (1 + $vat/100.0);
        
        $lineTotal = $priceGross * $qty;
        $totalAmount += $lineTotal;

        $orderItems[] = [
            'pid' => $pid,
            'qty' => $qty,
            'gross' => $priceGross
        ];
    }
    
    // Pobranie emaila użytkownika (do wysyłki potwierdzenia)
    $emailQ = mysqli_query($link, "SELECT email FROM users WHERE id=$userId LIMIT 1");
    $userEmail = '';
    if ($emailQ && $rowE = mysqli_fetch_assoc($emailQ)) {
        $userEmail = $rowE['email'];
    }

    // Insert Order
    // status domyslnie 'new'
    $sqlOrder = "INSERT INTO orders (user_id, status, total_amount, created_at) VALUES ($userId, 'new', $totalAmount, NOW())";
    if (!mysqli_query($link, $sqlOrder)) {
        return '<div class="card">Błąd zapisu zamówienia: '.cc_h(mysqli_error($link)).'</div>';
    }
    $orderId = mysqli_insert_id($link);

    // Insert Items
    foreach ($orderItems as $item) {
        $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price_gross) 
                    VALUES ($orderId, {$item['pid']}, {$item['qty']}, {$item['gross']})";
        mysqli_query($link, $sqlItem);
    }

    // Wyczysc koszyk
    cc_cart_clear();
    
    // Wysyłka maila potwierdzającego
    if ($userEmail !== '') {
        require_once __DIR__ . '/smtp_helper.php';
        $subject = "Potwierdzenie zamówienia #$orderId - ClickClick";
        $body = "Dziękujemy za złożenie zamówienia!\n\n"
              . "Numer zamówienia: #$orderId\n"
              . "Kwota do zapłaty: " . number_format($totalAmount, 2) . " zł\n\n"
              . "Pozdrawiamy,\nZespół ClickClick";
        send_smtp_mail($userEmail, $subject, $body);
    }

    return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
         . '<h2>Dziękujemy!</h2>'
         . '<p>Twoje zamówienie zostało przyjęte do realizacji. Numer zamówienia: <b>#'.$orderId.'</b>.</p>'
         . '<div style="margin-top:12px;">'
         . '<a class="btn" href="index.php">Wróć na stronę główną</a>'
         . '</div>'
         . '</div>';
}

// -----------------------------
// Widok koszyka (HTML)
// -----------------------------

function PokazKoszyk(): string {
  global $link;

  cc_cart_init();

  // Obsluga akcji (add/remove/update/clear/checkout)
  $action = isset($_GET['action']) ? $_GET['action'] : '';
  $action = preg_replace('/[^a-zA-Z0-9_\-]/', '', $action);

  // Zlozenie zamowienia
  if ($action === 'checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
      return cc_cart_checkout();
  }

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
       . '<p>: koszyk jest oparty na sesji (SESSION).</p>'
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
    // LIMIT dodajemy dla "bezpieczenstwa"
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
      // produkt usuniety z bazy
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
           . '<td class="cartColAct"><a class="linkDanger" href="index.php?idp=cart&action=remove&pid=' . (int)$pid . '" onclick="return confirm(\'Usunąć produkt z koszyka?\');">Usuń</a></td>'
           . '</tr>';
  }

  $summary = '<div class="cartSummary">'
           . '<div><b>Suma:</b> ' . number_format($sumTotal, 2, ',', ' ') . ' zł</div>'
           . '<div class="cartSummaryHint">Cena brutto (netto + VAT).</div>'
           . '</div>';

  $checkoutBtn = '';
  if (isset($_SESSION['user_id'])) {
      // Przycisk "Zloz zamowienie" tylko dla zalogowanych
      $checkoutBtn = '<form method="post" action="index.php?idp=cart&action=checkout" style="display:inline; margin-left:10px;">'
                   . '<button class="btn" style="background: #2563eb; border-color: #3b82f6;" type="submit" onclick="return confirm(\'Na pewno chcesz złożyć zamówienie?\')">Złóż zamówienie</button>'
                   . '</form>';
  }

  // Zawartosc formularza aktualizacji
  $formContent = '<div class="cartTableWrap">'
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
               . '<a class="btn secondary" href="index.php?idp=cart&action=clear" onclick="return confirm(\'Wyczyścić cały koszyk?\');">Wyczyść koszyk</a>'
               . '</div>';

  return '<form method="post" action="index.php?idp=cart&action=update">'
       . $formContent
       . '</form>'
       . ($checkoutBtn !== '' ? '<div style="margin-top:20px; text-align:right;">' . $checkoutBtn . '</div>' : '');
}
