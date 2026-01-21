<?php
// ================================
// ClickClick — showpage.php (Lab6)
// Ten plik pobiera tresc podstrony z bazy (tabela page_list).
// - bierzemy idp z URL
// - robimy SELECT ... LIMIT 1
// - jak status=1 to wyswietlamy page_content
// ================================

// ClickClick — v1.5 (Lab6)
// Pobieranie tresci podstrony z bazy danych (tabela: page_list)

if (!isset($link)) {
  // cfg.php nie zostal dolaczony
// Bez tego nie ma zmiennej $link (polaczenie z baza)
  die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
    . '<b>Blad konfiguracji:</b> brak polaczenia z baza (cfg.php).' 
    . '</div>');
}

// Bierzemy parametr idp i mapujemy go na page_title w bazie
// Czyli jak wchodze na index.php?idp=switches to szukamy page_title='switches'
// Uwaga: w tabeli page_list w kolumnie page_title wpisz te same wartosci.
$pageKey = isset($_GET['idp']) ? $_GET['idp'] : '';
$pageKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $pageKey);
if ($pageKey === '') {
  $pageKey = 'glowna';
}

// Zapytanie do bazy (LIMIT 1, bo chcemy tylko jedna strone)
$sql = 'SELECT page_title, page_content, status FROM page_list WHERE page_title = ? LIMIT 1';
$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
  die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
    . '<b>Blad zapytania SQL.</b> Nie udalo sie przygotowac zapytania.'
    . '</div>');
}

mysqli_stmt_bind_param($stmt, 's', $pageKey);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = $result ? mysqli_fetch_assoc($result) : null;

if (!$row) {
  echo '<div class="card">';
  echo '<b>Brak podstrony w bazie danych.</b><br />';
  echo 'Nie znaleziono rekordu o page_title = <code>' . htmlspecialchars($pageKey) . '</code>. '; 
  echo 'Dodaj rekord do tabeli <code>page_list</code> w bazie <code>moja_strona</code>.';
  echo '</div>';
} else {
  if ((int)$row['status'] !== 1) {
    echo '<div class="card"><b>Ta podstrona jest nieaktywna.</b></div>';
  } else {
    // page_content moze zawierac HTML (zapisany w bazie)
// Dlatego wyswietlamy go bez htmlspecialchars (bo ma byc normalny HTML)
    echo $row['page_content'];
  }
}

mysqli_stmt_close($stmt);
