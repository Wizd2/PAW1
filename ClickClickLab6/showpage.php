<?php
// ClickClick — v1.5 (Lab6)
// Pobieranie tresci podstrony z bazy danych (tabela: page_list)

if (!isset($link)) {
  // cfg.php nie zostal dolaczony
  die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
    . '<b>Blad konfiguracji:</b> brak polaczenia z baza (cfg.php).' 
    . '</div>');
}

// Mapowanie parametru idp na tytul w bazie
// Uwaga: w tabeli page_list w kolumnie page_title wpisz te same wartosci.
$pageKey = isset($_GET['idp']) ? $_GET['idp'] : '';
$pageKey = preg_replace('/[^a-zA-Z0-9_\-]/', '', $pageKey);
if ($pageKey === '') {
  $pageKey = 'glowna';
}

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
    echo $row['page_content'];
  }
}

mysqli_stmt_close($stmt);
