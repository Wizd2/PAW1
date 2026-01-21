<?php
// --- ClickClick helper: _cc_escape (ADMIN FIX) ---
if (!function_exists('_cc_escape')) {
    function _cc_escape(string $s): string {
        if (function_exists('h')) {
            return h($s);
        }
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// ================================
// ClickClick — admin/admin.php 
// Prosty panel CMS:
// - logowanie na sesji
// - lista podstron (SELECT)
// - dodawanie (INSERT)
// - edycja (UPDATE LIMIT 1)
// - usuwanie (DELETE LIMIT 1)
// Wszystko pracuje na tabeli page_list
// ================================

// ClickClick — v1.6 
// Prosty CMS: logowanie + CRUD dla tabeli page_list

// Start sesji - dzieki temu pamietamy czy admin jest zalogowany
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'remind') {
    echo '<section class="card"><h3>Przypomnienie hasła</h3>';
    if (!empty($_POST['login']) && !empty($_POST['email'])) {
        echo '<p>Administrator skontaktuje się z Państwem.</p>';
    } else {
        echo '<p>Wypełnij login oraz adres e-mail.</p>';
    }
    echo '<p><a href="admin.php">Powrót do logowania</a></p></section>';
    exit;
}


// Dolaczamy cfg.php, bo tam jest polaczenie z baza ($link) + login/haslo
require_once(__DIR__. '/../cfg.php');

// ---------------------------
// Helpers
// ---------------------------

// h() - krotka funkcja do bezpiecznego wyswietlania tekstu w HTML
function h($str) {
 return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Upload plikow do produktu (zdjecie / dzwiek)
// Zapisujemy do: /uploads/img oraz /uploads/audio
function cc_upload_file(string $fieldName, string $kind): string {
 $baseDir = realpath(__DIR__ . '/..');
 if ($baseDir === false) {
 return '';
 }

 if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
 return '';
 }

 $f = $_FILES[$fieldName];
 if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
 return '';
 }

 $tmp = (string)($f['tmp_name'] ?? '');
 $orig = (string)($f['name'] ?? '');
 if ($tmp === '' || $orig === '' || !is_uploaded_file($tmp)) {
 return '';
 }

 $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
 $allowedImg = ['jpg','jpeg','png','webp','gif'];
 $allowedAudio = ['wav','mp3','ogg'];

 if ($kind === 'img' && !in_array($ext, $allowedImg, true)) {
 return '';
 }
 if ($kind === 'audio' && !in_array($ext, $allowedAudio, true)) {
 return '';
 }

 $sub = ($kind === 'audio') ? 'uploads/audio' : 'uploads/img';
 $targetDir = $baseDir . '/' . $sub;
 if (!is_dir($targetDir)) {
 @mkdir($targetDir, 0775, true);
 }
 if (!is_dir($targetDir)) {
 return '';
 }

 // Prosta unikalna nazwa - bez polskich znakow i spacji
 $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', pathinfo($orig, PATHINFO_FILENAME));
 $safeBase = trim((string)$safeBase, '_');
 if ($safeBase === '') {
 $safeBase = 'file';
 }
 $newName = $safeBase . '_' . date('Ymd_His') . '_' . substr(md5((string)mt_rand()), 0, 6) . '.' . $ext;
 $destAbs = $targetDir . '/' . $newName;

 if (!move_uploaded_file($tmp, $destAbs)) {
 return '';
 }

 // Zwracamy sciezke wzgledna dla strony
 return $sub . '/' . $newName;
 }

// Sprawdzamy czy admin jest zalogowany (flaga w sesji)
function is_logged_in(): bool {
 return isset($_SESSION['logged']) && $_SESSION['logged'] === 1;
}

function require_login_or_show_form() {
 if (!is_logged_in()) {
 echo render_admin_layout('Logowanie', FormularzLogowania());
 exit;
 }
}

function render_admin_layout(string $title, string $contentHtml): string {
 $nav = '';
 if (is_logged_in()) {
 $nav = '
 <nav class="nav" aria-label="Admin menu">
 <a href="admin.php">Lista podstron</a>
 <a href="admin.php?action=add">Dodaj podstronę</a>
 <a href="admin.php?action=cat_list">Kategorie</a>
 <a href="admin.php?action=prod_list">Produkty</a>
 <a href="admin.php?action=videos_list">Filmy</a>
 <a href="admin.php?action=faq_list">FAQ</a>
 <a href="admin.php?action=guide_list">Poradnik</a>
 <a href="admin.php?action=logout">Wyloguj</a>
 </nav>';
 }

 return '<!DOCTYPE html>
 <html lang="pl">
 <head>
 <meta charset="UTF-8" />
 <meta http-equiv="Content-Language" content="pl" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>ClickClick Admin — '. h($title). '</title>
 <link rel="stylesheet" href="../css/style.css" />
 </head>
 <body>
 <div class="wrapper">
 <table class="layout">
 <tr>
 <td class="headerCell">
 <div class="brand">
 <div class="brandLeft">
 <div class="logo" aria-hidden="true"></div>
 <div class="brandTitle">
 <h1>ClickClick — Admin</h1>
 <p>CMS v2.1 </p>
 </div>
 </div>
 '. $nav. '
 </div>
 </td>
 </tr>
 <tr>
 <td class="contentCell">
 '. $contentHtml. '
 </td>
 </tr>
 <tr>
 <td class="footerCell">
 <div class="footerFlex">
 <small>Panel administracyjny — (Produkty + VAT)</small>
 
 <small><a href="../index.php">Powrót do sklepu</a></small>
 </div>
 </td>
 </tr>
 </table>
 </div>
 </body>
 </html>';
}

// ---------------------------
// : wymagane metody
// ---------------------------

function FormularzLogowania(): string {
 global $login, $pass;

 $err = '';

 // Proba logowania: jak formularz wyslal dane, to je sprawdzamy
 if (isset($_POST['do_login'])) {
 $u = (string)($_POST['login'] ?? '');
 $p = (string)($_POST['pass'] ?? '');

 if (hash_equals($login, $u) && hash_equals($pass, $p)) {
 $_SESSION['logged'] = 1;
 header('Location: admin.php');
 exit;
 }
 $err = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd:</b> nieprawidłowy login lub hasło.</div>';
 }

 $html = $err;
 $html.= '<section class="card" style="max-width:520px; margin:0 auto;">
 <h2>Logowanie</h2>
 <p>Zaloguj się, aby zarządzać podstronami (tabela <b>page_list</b>).</p>

 <form method="post" action="admin.php">
 <div style="margin-top:12px;">
 <label for="login"><b>Login</b></label><br />
 <input class="input" id="login" name="login" type="text" required />
 </div>
 <div style="margin-top:12px;">
 <label for="pass"><b>Hasło</b></label><br />
 <input class="input" id="pass" name="pass" type="password" required />
 </div>
 <div style="margin-top:12px;">
 <button class="btn" type="submit" name="do_login" value="1">Zaloguj</button>
 </div>
 </form>

 <p style="margin-top:12px; font-size:12px;">
 Dane logowania ustawisz w pliku <code>cfg.php</code> (zmienne <code>$login</code> i <code>$pass</code>).
 </p>
 </section>';

 return $html;
}

function ListaPodstron(): string {
 global $link;

 $q = mysqli_query($link, "SELECT id, page_title, status FROM page_list ORDER BY id ASC");

 $rows = '';
 if ($q) {
 while ($row = mysqli_fetch_assoc($q)) {
 $id = (int)$row['id'];
 $title = h($row['page_title']);
 $status = (int)$row['status'];

 $badge = $status === 1
 ? '<span class="badge" style="border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.10);">Aktywna</span>'
 : '<span class="badge" style="border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.10);">Nieaktywna</span>';

 $rows.= '<tr>'
. '<td>'. $id. '</td>'
. '<td><b>'. $title. '</b><div style="margin-top:6px;">'. $badge. '</div></td>'
. '<td style="white-space:nowrap;">'
. '<a class="btn" style="padding:8px 10px;" href="admin.php?action=edit&id='. $id. '">Edytuj</a> '
. '<a class="btn" style="padding:8px 10px; border-color: rgba(239,68,68,.55); background: rgba(239,68,68,.16);" '
. 'href="admin.php?action=delete&id='. $id. '" onclick="return confirm(\'Usunąć podstronę?\');">Usuń</a>'
. '</td>'
. '</tr>';
 }
 }

 if ($rows === '') {
 $rows = '<tr><td colspan="3">Brak rekordów w tabeli <b>page_list</b>.</td></tr>';
 }

 return '<section class="card">
 <h2>Lista podstron</h2>
 <p>Poniżej znajdują się podstrony z bazy danych. Możesz je edytować, usuwać lub dodać nową.</p>
 <div style="overflow:auto;">
 <table class="layout" style="border-radius:16px;">
 <tr>
 <td class="headerCell" style="padding:10px 12px;"><b>ID</b></td>
 <td class="headerCell" style="padding:10px 12px;"><b>Tytuł (page_title)</b></td>
 <td class="headerCell" style="padding:10px 12px;"><b>Akcje</b></td>
 </tr>
 '. $rows. '
 </table>
 </div>
 </section>';
}

function EdytujPodstrone(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 $msg = '';

 // Zapis zmian: po submit robimy UPDATE w bazie
 if (isset($_POST['do_save'])) {
 $title = trim((string)($_POST['page_title'] ?? ''));
 $content = (string)($_POST['page_content'] ?? '');
 $status = isset($_POST['status']) ? 1 : 0;

 $titleEsc = mysqli_real_escape_string($link, $title);
 $contentEsc = mysqli_real_escape_string($link, $content);

 $sql = "UPDATE page_list SET page_title='$titleEsc', page_content='$contentEsc', status=$status WHERE id=$id LIMIT 1";
 $ok = mysqli_query($link, $sql);

 if ($ok) {
 $msg = '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10); margin-bottom: 12px;">'
. '<b>Zapisano.</b> Zmiany zostały zapisane w bazie.</div>';
 } else {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd zapisu:</b> '. h(mysqli_error($link)). '</div>';
 }
 }

 // Pobieramy aktualne dane rekordu z bazy, zeby wypelnic formularz
 $q = mysqli_query($link, "SELECT id, page_title, page_content, status FROM page_list WHERE id=$id LIMIT 1");
 $row = $q ? mysqli_fetch_assoc($q) : null;

 if (!$row) {
 return '<div class="card">Nie znaleziono podstrony w bazie.</div>';
 }

 $title = h($row['page_title']);
 $content = h($row['page_content']);
 $checked = ((int)$row['status'] === 1) ? 'checked' : '';

 return $msg. '<section class="card">
 <h2>Edytuj podstronę (ID: '. $id. ')</h2>
 <p>Edytujesz rekord w tabeli <b>page_list</b>. Pamiętaj o poprawnym HTML w treści.</p>
 <form method="post" action="admin.php?action=edit&id='. $id. '">
 <div style="margin-top:12px;">
 <label for="page_title"><b>Tytuł / alias (page_title)</b></label><br />
 <input class="input" id="page_title" name="page_title" type="text" value="'. $title. '" required />
 </div>
 <div style="margin-top:12px;">
 <label for="page_content"><b>Treść (page_content)</b></label><br />
 <textarea id="page_content" name="page_content">'. $content. '</textarea>
 </div>
 <div style="margin-top:12px;">
 <label><input type="checkbox" name="status" value="1" '. $checked. ' /> Aktywna</label>
 </div>
 <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
 <button class="btn" type="submit" name="do_save" value="1">Zapisz</button>
 <a class="btn" href="admin.php" style="background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18);">Powrót</a>
 </div>
 </form>
 </section>';
}

function DodajNowaPodstrone(): string {
 global $link;

 $msg = '';

 $titleVal = '';
 $contentVal = '';
 $statusChecked = 'checked';

 if (isset($_POST['do_add'])) {
 $titleVal = trim((string)($_POST['page_title'] ?? ''));
 $contentVal = (string)($_POST['page_content'] ?? '');
 $status = isset($_POST['status']) ? 1 : 0;
 $statusChecked = $status === 1 ? 'checked' : '';

 $titleEsc = mysqli_real_escape_string($link, $titleVal);
 $contentEsc = mysqli_real_escape_string($link, $contentVal);

 $sql = "INSERT INTO page_list (page_title, page_content, status) VALUES ('$titleEsc', '$contentEsc', $status)";
 $ok = mysqli_query($link, $sql);

 if ($ok) {
 $newId = (int)mysqli_insert_id($link);
 $msg = '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10); margin-bottom: 12px;">'
. '<b>Dodano.</b> Nowa podstrona została utworzona (ID: '. $newId. ').</div>';

 // Wyczyść formularz po dodaniu
 $titleVal = '';
 $contentVal = '';
 $statusChecked = 'checked';
 } else {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd dodawania:</b> '. h(mysqli_error($link)). '</div>';
 }
 }

 return $msg. '<section class="card">
 <h2>Dodaj nową podstronę</h2>
 <p>Tworzysz nowy rekord w tabeli <b>page_list</b> (INSERT).</p>
 <form method="post" action="admin.php?action=add">
 <div style="margin-top:12px;">
 <label for="page_title"><b>Tytuł / alias (page_title)</b></label><br />
 <input class="input" id="page_title" name="page_title" type="text" value="'. h($titleVal). '" placeholder="np. nowa_strona" required />
 </div>
 <div style="margin-top:12px;">
 <label for="page_content"><b>Treść (page_content)</b></label><br />
 <textarea id="page_content" name="page_content" placeholder="Wklej HTML treści...">'. h($contentVal). '</textarea>
 </div>
 <div style="margin-top:12px;">
 <label><input type="checkbox" name="status" value="1" '. $statusChecked. ' /> Aktywna</label>
 </div>
 <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
 <button class="btn" type="submit" name="do_add" value="1">Dodaj</button>
 <a class="btn" href="admin.php" style="background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18);">Powrót</a>
 </div>
 </form>
 </section>';
}

function UsunPodstrone(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 $sql = "DELETE FROM page_list WHERE id=$id LIMIT 1";
 $ok = mysqli_query($link, $sql);

 if ($ok) {
 return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
. '<b>Usunięto.</b> Rekord został usunięty z bazy.</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php">Powrót do listy</a></div>';
 }

 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd usuwania:</b> '. h(mysqli_error($link)). '</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php">Powrót do listy</a></div>';
}

// ---------------------------
// : Kategorie sklepu (tabela categories)
// ---------------------------

// Pobieramy liste kategorii do selecta (zeby wybrac parent_id)
function cc_cat_options(int $selectedId = 0): string {
 global $link;

 $opt = '<option value="0">(brak - kategoria główna)</option>';
 $q = mysqli_query($link, "SELECT id, name FROM categories ORDER BY parent_id ASC, name ASC");
 if ($q) {
 while ($row = mysqli_fetch_assoc($q)) {
 $id = (int)$row['id'];
 $name = h($row['name']);
 $sel = ($id === $selectedId) ? 'selected' : '';
 $opt.= '<option value="'. $id. '" '. $sel. '>'. $id. ' — '. $name. '</option>';
 }
 }
 return $opt;
}

function ListaKategorii(): string {
 global $link;

 $rows = '';
 $q = mysqli_query($link, "SELECT c.id, c.parent_id, c.name, p.name AS parent_name
 FROM categories c
 LEFT JOIN categories p ON p.id = c.parent_id
 ORDER BY c.parent_id ASC, c.name ASC");

 if ($q) {
 while ($row = mysqli_fetch_assoc($q)) {
 $id = (int)$row['id'];
 $parentId = (int)$row['parent_id'];
 $name = h($row['name']);
 $parentName = $row['parent_name'] ? h($row['parent_name']) : '-';

 $rows.= '<tr>'
. '<td>'. $id. '</td>'
. '<td><b>'. $name. '</b><div style="margin-top:6px; color: rgba(255,255,255,.65);">Parent: '. $parentName. ' (ID: '. $parentId. ')</div></td>'
. '<td style="white-space:nowrap;">'
. '<a class="btn" style="padding:8px 10px;" href="admin.php?action=cat_edit&id='. $id. '">Edytuj</a> '
. '<a class="btn" style="padding:8px 10px; border-color: rgba(239,68,68,.55); background: rgba(239,68,68,.16);" '
. 'href="admin.php?action=cat_delete&id='. $id. '" onclick="return confirm(\'Usunąć kategorię?\');">Usuń</a>'
. '</td>'
. '</tr>';
 }
 }

 if ($rows === '') {
 $rows = '<tr><td colspan="3">Brak kategorii w tabeli <b>categories</b>. Zaimportuj <code>lab10_categories_template.sql</code>.</td></tr>';
 }

 return '<section class="card">
 <h2>Kategorie sklepu </h2>
 <p>W robimy drzewo kategorii. Tu zarządzasz tabelą <b>categories</b>.</p>
 <div style="margin:10px 0;">
 <a class="btn" href="admin.php?action=cat_add">+ Dodaj kategorię</a>
 </div>
 <div style="overflow:auto;">
 <table class="layout" style="border-radius:16px;">
 <tr>
 <td class="headerCell" style="padding:10px 12px;"><b>ID</b></td>
 <td class="headerCell" style="padding:10px 12px;"><b>Nazwa</b></td>
 <td class="headerCell" style="padding:10px 12px;"><b>Akcje</b></td>
 </tr>
 '. $rows. '
 </table>
 </div>
 <p style="margin-top:12px;">Podgląd na stronie: <a href="../index.php?idp=kategorie">/index.php?idp=kategorie</a></p>
 </section>';
}

function DodajKategorie(): string {
 global $link;

 $msg = '';
 $nameVal = '';
 $parentVal = 0;

 if (isset($_POST['do_add_cat'])) {
 $nameVal = trim((string)($_POST['name'] ?? ''));
 $parentVal = (int)($_POST['parent_id'] ?? 0);

 if ($nameVal === '') {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd:</b> nazwa nie może być pusta.</div>';
 } else {
 $nameEsc = mysqli_real_escape_string($link, $nameVal);
 $sql = "INSERT INTO categories (parent_id, name) VALUES ($parentVal, '$nameEsc')";
 $ok = mysqli_query($link, $sql);

 if ($ok) {
 $msg = '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10); margin-bottom: 12px;">'
. '<b>Dodano kategorię.</b></div>';
 $nameVal = '';
 $parentVal = 0;
 } else {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>';
 }
 }
 }

 return $msg. '<section class="card" style="max-width:720px;">
 <h2>Dodaj kategorię</h2>
 <p>Dodajesz rekord do tabeli <b>categories</b> (INSERT).</p>
 <form method="post" action="admin.php?action=cat_add">
 <div style="margin-top:12px;">
 <label for="name"><b>Nazwa kategorii</b></label><br />
 <input class="input" id="name" name="name" type="text" value="'. h($nameVal). '" required />
 </div>
 <div style="margin-top:12px;">
 <label for="parent_id"><b>Rodzic (parent_id)</b></label><br />
 <select class="input" id="parent_id" name="parent_id">'. cc_cat_options($parentVal). '</select>
 </div>
 <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
 <button class="btn" type="submit" name="do_add_cat" value="1">Dodaj</button>
 <a class="btn" href="admin.php?action=cat_list" style="background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18);">Powrót</a>
 </div>
 </form>
 </section>';
}

function EdytujKategorie(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 $msg = '';

 if (isset($_POST['do_save_cat'])) {
 $name = trim((string)($_POST['name'] ?? ''));
 $parent = (int)($_POST['parent_id'] ?? 0);

 if ($name === '') {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd:</b> nazwa nie może być pusta.</div>';
 } else {
 $nameEsc = mysqli_real_escape_string($link, $name);
 $sql = "UPDATE categories SET parent_id=$parent, name='$nameEsc' WHERE id=$id LIMIT 1";
 $ok = mysqli_query($link, $sql);
 if ($ok) {
 $msg = '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10); margin-bottom: 12px;">'
. '<b>Zapisano.</b></div>';
 } else {
 $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>';
 }
 }
 }

 $q = mysqli_query($link, "SELECT id, parent_id, name FROM categories WHERE id=$id LIMIT 1");
 $row = $q ? mysqli_fetch_assoc($q) : null;
 if (!$row) {
 return '<div class="card">Nie znaleziono kategorii.</div>';
 }

 $nameVal = h($row['name']);
 $parentVal = (int)$row['parent_id'];

 return $msg. '<section class="card" style="max-width:720px;">
 <h2>Edytuj kategorię (ID: '. $id. ')</h2>
 <form method="post" action="admin.php?action=cat_edit&id='. $id. '">
 <div style="margin-top:12px;">
 <label for="name"><b>Nazwa</b></label><br />
 <input class="input" id="name" name="name" type="text" value="'. $nameVal. '" required />
 </div>
 <div style="margin-top:12px;">
 <label for="parent_id"><b>Rodzic (parent_id)</b></label><br />
 <select class="input" id="parent_id" name="parent_id">'. cc_cat_options($parentVal). '</select>
 </div>
 <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
 <button class="btn" type="submit" name="do_save_cat" value="1">Zapisz</button>
 <a class="btn" href="admin.php?action=cat_list" style="background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18);">Powrót</a>
 </div>
 </form>
 </section>';
}

function UsunKategorie(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 // Najprostsze usuwanie: kasujemy rekord. Dzieci zostaną "osierocone" (parent_id dalej pokazuje na usuniety ID).
 // Na labach to wystarczy, a jak ktos chce, to moze potem dopracowac.
 $sql = "DELETE FROM categories WHERE id=$id LIMIT 1";
 $ok = mysqli_query($link, $sql);

 if ($ok) {
 return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
. '<b>Usunięto kategorię.</b></div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=cat_list">Powrót</a></div>';
 }

 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=cat_list">Powrót</a></div>';
}

// ---------------------------
// : Produkty sklepu (tabela products)
// ---------------------------

// Opcje kategorii do selecta (tu uzywamy categories)
function cc_cat_options_simple(int $selected_id = 0): string {
 global $link;
 $html = '';

 $q = mysqli_query($link, "SELECT id, parent_id, name FROM categories ORDER BY parent_id ASC, name ASC");
 if (!$q) {
 return '<option value="0">(brak)</option>';
 }

 while ($row = mysqli_fetch_assoc($q)) {
 $id = (int)$row['id'];
 $name = h($row['name']);
 $sel = ($id === (int)$selected_id) ? ' selected' : '';
 $html.= '<option value="'. $id. '"'. $sel. '>'. $name. '</option>';
 }

 return $html;
}

function ListaProduktow(): string {
 global $link;

 $q = mysqli_query(
 $link,
 "SELECT p.id, p.title, p.price_netto, p.vat, p.stock_qty, p.status, c.name AS cat_name
 FROM products p
 LEFT JOIN categories c ON c.id = p.category_id
 ORDER BY p.id DESC"
 );

 if (!$q) {
 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd SQL:</b> '. h(mysqli_error($link))
. '</div>';
 }

 $rows = '';
 while ($r = mysqli_fetch_assoc($q)) {
 $id = (int)$r['id'];
 $title = h($r['title']);
 $cat = h($r['cat_name'] ?? '');
 $netto = number_format((float)$r['price_netto'], 2, ',', ' ');
 $vat = (int)$r['vat'];
 $qty = (int)$r['stock_qty'];
 $status = ((int)$r['status'] === 1) ? 'AKTYWNY' : 'OFF';

 $rows.= '<tr>'
. '<td>'. $id. '</td>'
. '<td><b>'. $title. '</b><br /><span style="color: rgba(255,255,255,.65); font-size:12px;">'. $cat. '</span></td>'
. '<td>'. $netto. ' zł</td>'
. '<td>'. $vat. '%</td>'
. '<td>'. $qty. '</td>'
. '<td>'. $status. '</td>'
. '<td style="white-space:nowrap;">'
. '<a class="btn" href="admin.php?action=prod_edit&id='. $id. '">Edytuj</a> '
. '<a class="btn" href="admin.php?action=prod_delete&id='. $id. '" onclick="return confirm(\'Usunąć produkt?\')" style="background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.45);">Usuń</a>'
. '</td>'
. '</tr>';
 }

 return '<section class="card">'
. '<h2>Produkty </h2>'
. '<p>Tu zarządzasz produktami w tabeli <code>products</code>. Cena brutto liczy się na stronie sklepu.</p>'
. '<div style="margin: 10px 0 12px;"><a class="btn" href="admin.php?action=prod_add">Dodaj produkt</a></div>'
. '<div style="overflow:auto;">'
. '<table style="width:100%; border-collapse:collapse;">'
. '<tr style="text-align:left; border-bottom:1px solid rgba(255,255,255,.12);">'
. '<th style="padding:10px;">ID</th>'
. '<th style="padding:10px;">Nazwa</th>'
. '<th style="padding:10px;">Netto</th>'
. '<th style="padding:10px;">VAT</th>'
. '<th style="padding:10px;">Szt.</th>'
. '<th style="padding:10px;">Status</th>'
. '<th style="padding:10px;">Akcje</th>'
. '</tr>'
. $rows
. '</table>'
. '</div>'
. '</section>';
}

function ProduktForm(array $values, string $mode, int $id = 0): string {
 // mode = add/edit
 $title = h($values['title'] ?? '');
 $desc = h($values['description'] ?? '');
 $netto = h((string)($values['price_netto'] ?? ''));
 $vat = h((string)($values['vat'] ?? '23'));
 $qty = h((string)($values['stock_qty'] ?? '0'));
 $status = ((int)($values['status'] ?? 1) === 1) ? 'checked' : '';
 $catId = (int)($values['category_id'] ?? 0);
 $image = h($values['image_url'] ?? '');
 $sound = h($values['sound_file'] ?? '');
 $s1 = h($values['spec_1'] ?? '');
 $s2 = h($values['spec_2'] ?? '');
 $s3 = h($values['spec_3'] ?? '');
 $s4 = h($values['spec_4'] ?? '');

 $action = ($mode === 'edit') ? 'prod_edit&id='. $id : 'prod_add';
 $headline = ($mode === 'edit') ? 'Edytuj produkt' : 'Dodaj produkt';

 return '<section class="card">'
. '<h2>'. $headline. '</h2>'
 . '<form method="post" enctype="multipart/form-data" action="admin.php?action='. $action. '">' 
. '<div class="formGrid">'
. '<div>'
. '<label><b>Nazwa produktu</b></label><br />'
. '<input class="input" type="text" name="title" value="'. $title. '" required />'
. '</div>'
. '<div>'
. '<label><b>Kategoria</b></label><br />'
. '<select class="input" name="category_id" required>'
. cc_cat_options_simple($catId)
. '</select>'
. '</div>'
. '</div>'

. '<div style="margin-top:12px;">'
. '<label><b>Opis</b></label><br />'
. '<textarea class="input" name="description" rows="5" required>'. $desc. '</textarea>'
. '</div>'

. '<div class="formGrid" style="margin-top:12px;">'
. '<div><label><b>Cena netto</b></label><br /><input class="input" type="number" step="0.01" name="price_netto" value="'. $netto. '" required /></div>'
. '<div><label><b>VAT (%)</b></label><br /><input class="input" type="number" name="vat" value="'. $vat. '" required /></div>'
. '</div>'

. '<div class="formGrid" style="margin-top:12px;">'
. '<div><label><b>Stan magazynu (szt.)</b></label><br /><input class="input" type="number" name="stock_qty" value="'. $qty. '" required /></div>'
. '<div style="display:flex;align-items:center; gap:10px; padding-top:20px;">'
. '<input type="checkbox" id="status" name="status" value="1" '. $status. ' />'
. '<label for="status"><b>Aktywny</b> (status)</label>'
. '</div>'
. '</div>'

 . '<input type="hidden" name="current_image_url" value="'. $image. '" />'
 . '<input type="hidden" name="current_sound_file" value="'. $sound. '" />'

 . '<div class="formGrid" style="margin-top:12px;">'
 . '<div>'
 . '<label><b>Zdjęcie produktu</b></label><br />'
 . '<input class="input" type="file" name="image_file" accept="image/*" />'
 . ($image !== '' ? '<div class="muted" style="margin-top:6px;">Aktualne: <code>'. $image. '</code></div>' : '<div class="muted" style="margin-top:6px;">Brak zdjęcia (opcjonalnie)</div>')
 . '</div>'
 . '<div>'
 . '<label><b>Dźwięk switcha</b></label><br />'
 . '<input class="input" type="file" name="sound_file_upload" accept="audio/*" />'
 . ($sound !== '' ? '<div class="muted" style="margin-top:6px;">Aktualne: <code>'. $sound. '</code></div>' : '<div class="muted" style="margin-top:6px;">Brak dźwięku (opcjonalnie)</div>')
 . '</div>'
 . '</div>'

. '<h3 style="margin-top:16px;">Specy (4 linie)</h3>'
. '<div class="formGrid">'
. '<div><input class="input" type="text" name="spec_1" value="'. $s1. '" placeholder="np. Typ: <b>Linear</b>" /></div>'
. '<div><input class="input" type="text" name="spec_2" value="'. $s2. '" placeholder="np. Actuation: <b>2.0 mm</b>" /></div>'
. '</div>'
. '<div class="formGrid" style="margin-top:10px;">'
. '<div><input class="input" type="text" name="spec_3" value="'. $s3. '" placeholder="np. Travel: <b>4.0 mm</b>" /></div>'
. '<div><input class="input" type="text" name="spec_4" value="'. $s4. '" placeholder="np. Force: <b>67g</b>" /></div>'
. '</div>'

. '<div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">'
. '<button class="btn" type="submit" name="do_save_product" value="1">Zapisz</button>'
. '<a class="btn" href="admin.php?action=prod_list" style="background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18);">Powrót</a>'
. '</div>'
. '</form>'
. '</section>';
}

function DodajProdukt(): string {
 global $link;

 if (isset($_POST['do_save_product'])) {
 // Pobieramy dane z formularza
 $title = mysqli_real_escape_string($link, (string)($_POST['title'] ?? ''));
 $desc = mysqli_real_escape_string($link, (string)($_POST['description'] ?? ''));
 $catId = (int)($_POST['category_id'] ?? 0);
 $netto = (float)($_POST['price_netto'] ?? 0);
 $vat = (int)($_POST['vat'] ?? 23);
 $qty = (int)($_POST['stock_qty'] ?? 0);
 $status = isset($_POST['status']) ? 1 : 0;
 // Uploady (opcjonalne). Jak user nic nie wrzuci - zostaje pusto.
 $uploadedImg = cc_upload_file('image_file', 'img');
 $uploadedAudio = cc_upload_file('sound_file_upload', 'audio');
 $image = mysqli_real_escape_string($link, $uploadedImg);
 $sound = mysqli_real_escape_string($link, $uploadedAudio);
 $s1 = mysqli_real_escape_string($link, (string)($_POST['spec_1'] ?? ''));
 $s2 = mysqli_real_escape_string($link, (string)($_POST['spec_2'] ?? ''));
 $s3 = mysqli_real_escape_string($link, (string)($_POST['spec_3'] ?? ''));
 $s4 = mysqli_real_escape_string($link, (string)($_POST['spec_4'] ?? ''));

 $sql = "INSERT INTO products (title, description, price_netto, vat, stock_qty, status, category_id, image_url, sound_file, spec_1, spec_2, spec_3, spec_4)
 VALUES ('$title', '$desc', $netto, $vat, $qty, $status, $catId, '$image', '$sound', '$s1', '$s2', '$s3', '$s4')";

 $ok = mysqli_query($link, $sql);
 if ($ok) {
 return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
. '<b>Dodano produkt.</b></div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót do listy</a></div>';
 }

 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót</a></div>';
 }

 return ProduktForm([
 'vat' => 23,
 'stock_qty' => 0,
 'status' => 1,
 ], 'add');
}

function EdytujProdukt(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 if (isset($_POST['do_save_product'])) {
 $title = mysqli_real_escape_string($link, (string)($_POST['title'] ?? ''));
 $desc = mysqli_real_escape_string($link, (string)($_POST['description'] ?? ''));
 $catId = (int)($_POST['category_id'] ?? 0);
 $netto = (float)($_POST['price_netto'] ?? 0);
 $vat = (int)($_POST['vat'] ?? 23);
 $qty = (int)($_POST['stock_qty'] ?? 0);
 $status = isset($_POST['status']) ? 1 : 0;
 // Bierzemy poprzednie sciezki (gdy nie wrzucimy nowego pliku)
 $currentImage = (string)($_POST['current_image_url'] ?? '');
 $currentSound = (string)($_POST['current_sound_file'] ?? '');

 $uploadedImg = cc_upload_file('image_file', 'img');
 $uploadedAudio = cc_upload_file('sound_file_upload', 'audio');

 $imagePath = ($uploadedImg !== '') ? $uploadedImg : $currentImage;
 $soundPath = ($uploadedAudio !== '') ? $uploadedAudio : $currentSound;

 $image = mysqli_real_escape_string($link, $imagePath);
 $sound = mysqli_real_escape_string($link, $soundPath);
 $s1 = mysqli_real_escape_string($link, (string)($_POST['spec_1'] ?? ''));
 $s2 = mysqli_real_escape_string($link, (string)($_POST['spec_2'] ?? ''));
 $s3 = mysqli_real_escape_string($link, (string)($_POST['spec_3'] ?? ''));
 $s4 = mysqli_real_escape_string($link, (string)($_POST['spec_4'] ?? ''));

 $sql = "UPDATE products
 SET title='$title', description='$desc', category_id=$catId, price_netto=$netto, vat=$vat,
 stock_qty=$qty, status=$status, image_url='$image', sound_file='$sound',
 spec_1='$s1', spec_2='$s2', spec_3='$s3', spec_4='$s4', updated_at=NOW()
 WHERE id=$id LIMIT 1";

 $ok = mysqli_query($link, $sql);
 if ($ok) {
 return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
. '<b>Zapisano zmiany.</b></div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót do listy</a></div>';
 }

 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót</a></div>';
 }

 $q = mysqli_query($link, "SELECT * FROM products WHERE id=$id LIMIT 1");
 if (!$q) {
 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd SQL:</b> '. h(mysqli_error($link)). '</div>';
 }
 $row = mysqli_fetch_assoc($q);
 if (!$row) {
 return '<div class="card">Nie znaleziono produktu.</div>';
 }

 return ProduktForm($row, 'edit', $id);
}

function UsunProdukt(int $id): string {
 global $link;

 $id = (int)$id;
 if ($id <= 0) {
 return '<div class="card">Nieprawidłowe ID.</div>';
 }

 $ok = mysqli_query($link, "DELETE FROM products WHERE id=$id LIMIT 1");
 if ($ok) {
 return '<div class="card" style="border-color: rgba(34,197,94,.45); background: rgba(34,197,94,.10);">'
. '<b>Usunięto produkt.</b></div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót do listy</a></div>';
 }

 return '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08);">'
. '<b>Błąd:</b> '. h(mysqli_error($link)). '</div>'
. '<div style="margin-top:12px;"><a class="btn" href="admin.php?action=prod_list">Powrót</a></div>';
}

// ---------------------------
// Routing
// ---------------------------


// ===================================
// Filmy / FAQ / Poradnik — prosty CRUD
// ===================================

function _cc_post(string $key): string {
  return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function ListaFilmow(): string {
  global $link;
  $html = '<div class="card"><h2>Filmy</h2><p><a class="btn" href="admin.php?action=videos_add">Dodaj film</a></p>';
  $res = mysqli_query($link, "SELECT id,title,youtube_url,status,created_at FROM videos ORDER BY id DESC");
  if (!$res) {
    return $html . '<p>Brak tabeli videos. Zaimportuj plik SQL z projektu.</p></div>';
  }
  $html .= '<table class="table"><tr><th>ID</th><th>Tytuł</th><th>Link</th><th>Status</th><th>Akcje</th></tr>';
  while ($row = mysqli_fetch_assoc($res)) {
    $id = (int)$row['id'];
    $title = _cc_escape($row['title']);
    $url = _cc_escape($row['youtube_url']);
    $st = ((int)$row['status'] === 1) ? 'AKTYWNY' : 'UKRYTY';
    $html .= "<tr><td>$id</td><td><b>$title</b></td><td><a href=\"$url\" target=\"_blank\">YouTube</a></td><td>$st</td>
      <td><a class=\"btn\" href=\"admin.php?action=videos_edit&id=$id\">Edytuj</a>
          <a class=\"btn danger\" href=\"admin.php?action=videos_delete&id=$id\" onclick=\"return confirm('Usunąć?')\">Usuń</a></td></tr>";
  }
  $html .= '</table></div>';
  return $html;
}

function FilmForm(array $row, string $mode, int $id=0): string {
  $title = _cc_escape($row['title'] ?? '');
  $url = _cc_escape($row['youtube_url'] ?? '');
  $status = (int)($row['status'] ?? 1);
  $checked = $status === 1 ? 'checked' : '';
  $action = $mode === 'edit' ? "admin.php?action=videos_edit&id=$id" : "admin.php?action=videos_add";
  $h = ($mode === 'edit') ? 'Edytuj film' : 'Dodaj film';
  return '<div class="card"><h2>'.$h.'</h2>
    <form method="post" action="'.$action.'">
      <label><b>Tytuł</b></label><br><input class="input" type="text" name="title" value="'.$title.'" required><br><br>
      <label><b>Link YouTube</b></label><br><input class="input" type="url" name="youtube_url" value="'.$url.'" placeholder="https://www.youtube.com/watch?v=..." required><br><br>
      <label><input type="checkbox" name="status" '.$checked.'> Aktywny</label><br><br>
      <button class="btn" type="submit">Zapisz</button>
      <a class="btn" href="admin.php?action=videos_list">Anuluj</a>
    </form></div>';
}

function DodajFilm(): string {
  global $link;
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = _cc_escape(_cc_post('title'));
    $url = _cc_escape(_cc_post('youtube_url'));
    $status = isset($_POST['status']) ? 1 : 0;
    mysqli_query($link, "INSERT INTO videos(title,youtube_url,status) VALUES('$title','$url',$status)");
    return '<div class="card">Zapisano. <a href="admin.php?action=videos_list">Wróć</a></div>';
  }
  return FilmForm(['status'=>1],'add');
}

function EdytujFilm(int $id): string {
  global $link;
  $id = (int)$id;
  if ($id<=0) return '<div class="card">Nieprawidłowe ID.</div>';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = _cc_escape(_cc_post('title'));
    $url = _cc_escape(_cc_post('youtube_url'));
    $status = isset($_POST['status']) ? 1 : 0;
    mysqli_query($link, "UPDATE videos SET title='$title', youtube_url='$url', status=$status WHERE id=$id LIMIT 1");
    return '<div class="card">Zapisano. <a href="admin.php?action=videos_list">Wróć</a></div>';
  }
  $res = mysqli_query($link, "SELECT * FROM videos WHERE id=$id LIMIT 1");
  $row = $res ? mysqli_fetch_assoc($res) : null;
  if (!$row) return '<div class="card">Nie znaleziono filmu.</div>';
  return FilmForm($row,'edit',$id);
}

function UsunFilm(int $id): string {
  global $link;
  $id=(int)$id;
  mysqli_query($link, "DELETE FROM videos WHERE id=$id LIMIT 1");
  return '<div class="card">Usunięto. <a href="admin.php?action=videos_list">Wróć</a></div>';
}

// FAQ
function ListaFAQ(): string {
  global $link;
  $html = '<div class="card"><h2>FAQ</h2><p><a class="btn" href="admin.php?action=faq_add">Dodaj wpis</a></p>';
  $res = mysqli_query($link, "SELECT id,question,status,sort_order FROM faq ORDER BY sort_order ASC, id ASC");
  if (!$res) return $html . '<p>Brak tabeli faq. Zaimportuj plik SQL z projektu.</p></div>';
  $html .= '<table class="table"><tr><th>ID</th><th>Pytanie</th><th>Kolejność</th><th>Status</th><th>Akcje</th></tr>';
  while ($row=mysqli_fetch_assoc($res)) {
    $id=(int)$row['id'];
    $q=_cc_escape($row['question']);
    $ord=(int)$row['sort_order'];
    $st=((int)$row['status']===1)?'AKTYWNY':'UKRYTY';
    $html.="<tr><td>$id</td><td>$q</td><td>$ord</td><td>$st</td>
      <td><a class=\"btn\" href=\"admin.php?action=faq_edit&id=$id\">Edytuj</a>
          <a class=\"btn danger\" href=\"admin.php?action=faq_delete&id=$id\" onclick=\"return confirm('Usunąć?')\">Usuń</a></td></tr>";
  }
  $html.='</table></div>';
  return $html;
}

function FaqForm(array $row, string $mode, int $id=0): string {
  $q=_cc_escape($row['question']??'');
  $a=_cc_escape($row['answer']??'');
  $ord=(int)($row['sort_order']??0);
  $status=(int)($row['status']??1);
  $checked=$status===1?'checked':'';
  $action=$mode==='edit' ? "admin.php?action=faq_edit&id=$id" : "admin.php?action=faq_add";
  $h=$mode==='edit'?'Edytuj FAQ':'Dodaj FAQ';
  return '<div class="card"><h2>'.$h.'</h2>
    <form method="post" action="'.$action.'">
      <label><b>Pytanie</b></label><br><input class="input" type="text" name="question" value="'.$q.'" required><br><br>
      <label><b>Odpowiedź</b></label><br><textarea name="answer" required style="width:100%; min-height:120px;">'.$a.'</textarea><br><br>
      <label><b>Kolejność</b></label><br><input class="input" type="number" name="sort_order" value="'.$ord.'"><br><br>
      <label><input type="checkbox" name="status" '.$checked.'> Aktywny</label><br><br>
      <button class="btn" type="submit">Zapisz</button>
      <a class="btn" href="admin.php?action=faq_list">Anuluj</a>
    </form></div>';
}

function DodajFAQ(): string {
  global $link;
  if ($_SERVER['REQUEST_METHOD']==='POST') {
    $q=_cc_escape(_cc_post('question'));
    $a=_cc_escape(_cc_post('answer'));
    $ord=(int)_cc_post('sort_order');
    $status=isset($_POST['status'])?1:0;
    mysqli_query($link, "INSERT INTO faq(question,answer,sort_order,status) VALUES('$q','$a',$ord,$status)");
    return '<div class="card">Zapisano. <a href="admin.php?action=faq_list">Wróć</a></div>';
  }
  return FaqForm(['status'=>1,'sort_order'=>0],'add');
}

function EdytujFAQ(int $id): string {
  global $link;
  $id=(int)$id;
  if ($id<=0) return '<div class="card">Nieprawidłowe ID.</div>';
  if ($_SERVER['REQUEST_METHOD']==='POST') {
    $q=_cc_escape(_cc_post('question'));
    $a=_cc_escape(_cc_post('answer'));
    $ord=(int)_cc_post('sort_order');
    $status=isset($_POST['status'])?1:0;
    mysqli_query($link, "UPDATE faq SET question='$q', answer='$a', sort_order=$ord, status=$status WHERE id=$id LIMIT 1");
    return '<div class="card">Zapisano. <a href="admin.php?action=faq_list">Wróć</a></div>';
  }
  $res=mysqli_query($link, "SELECT * FROM faq WHERE id=$id LIMIT 1");
  $row=$res?mysqli_fetch_assoc($res):null;
  if (!$row) return '<div class="card">Nie znaleziono wpisu.</div>';
  return FaqForm($row,'edit',$id);
}

function UsunFAQ(int $id): string {
  global $link;
  $id=(int)$id;
  mysqli_query($link, "DELETE FROM faq WHERE id=$id LIMIT 1");
  return '<div class="card">Usunięto. <a href="admin.php?action=faq_list">Wróć</a></div>';
}

// Poradnik
function ListaPoradnik(): string {
  global $link;
  $html='<div class="card"><h2>Poradnik</h2><p><a class="btn" href="admin.php?action=guide_add">Dodaj artykuł</a></p>';
  $res=mysqli_query($link, "SELECT id,title,status,created_at FROM guides ORDER BY id DESC");
  if (!$res) return $html.'<p>Brak tabeli guides. Zaimportuj plik SQL z projektu.</p></div>';
  $html.='<table class="table"><tr><th>ID</th><th>Tytuł</th><th>Status</th><th>Akcje</th></tr>';
  while ($row=mysqli_fetch_assoc($res)) {
    $id=(int)$row['id'];
    $t=_cc_escape($row['title']);
    $st=((int)$row['status']===1)?'AKTYWNY':'UKRYTY';
    $html.="<tr><td>$id</td><td><b>$t</b></td><td>$st</td>
      <td><a class=\"btn\" href=\"admin.php?action=guide_edit&id=$id\">Edytuj</a>
          <a class=\"btn danger\" href=\"admin.php?action=guide_delete&id=$id\" onclick=\"return confirm('Usunąć?')\">Usuń</a></td></tr>";
  }
  $html.='</table></div>';
  return $html;
}

function GuideForm(array $row, string $mode, int $id=0): string {
  $t=_cc_escape($row['title']??'');
  $b=_cc_escape($row['body']??'');
  $status=(int)($row['status']??1);
  $checked=$status===1?'checked':'';
  $action=$mode==='edit' ? "admin.php?action=guide_edit&id=$id" : "admin.php?action=guide_add";
  $h=$mode==='edit'?'Edytuj artykuł':'Dodaj artykuł';
  return '<div class="card"><h2>'.$h.'</h2>
    <form method="post" action="'.$action.'">
      <label><b>Tytuł</b></label><br><input class="input" type="text" name="title" value="'.$t.'" required><br><br>
      <label><b>Treść</b></label><br><textarea name="body" required style="width:100%; min-height:180px;">'.$b.'</textarea><br><br>
      <label><input type="checkbox" name="status" '.$checked.'> Aktywny</label><br><br>
      <button class="btn" type="submit">Zapisz</button>
      <a class="btn" href="admin.php?action=guide_list">Anuluj</a>
    </form></div>';
}

function DodajPoradnik(): string {
  global $link;
  if ($_SERVER['REQUEST_METHOD']==='POST') {
    $t=_cc_escape(_cc_post('title'));
    $b=_cc_escape(_cc_post('body'));
    $status=isset($_POST['status'])?1:0;
    mysqli_query($link, "INSERT INTO guides(title,body,status) VALUES('$t','$b',$status)");
    return '<div class="card">Zapisano. <a href="admin.php?action=guide_list">Wróć</a></div>';
  }
  return GuideForm(['status'=>1],'add');
}

function EdytujPoradnik(int $id): string {
  global $link;
  $id=(int)$id;
  if ($id<=0) return '<div class="card">Nieprawidłowe ID.</div>';
  if ($_SERVER['REQUEST_METHOD']==='POST') {
    $t=_cc_escape(_cc_post('title'));
    $b=_cc_escape(_cc_post('body'));
    $status=isset($_POST['status'])?1:0;
    mysqli_query($link, "UPDATE guides SET title='$t', body='$b', status=$status WHERE id=$id LIMIT 1");
    return '<div class="card">Zapisano. <a href="admin.php?action=guide_list">Wróć</a></div>';
  }
  $res=mysqli_query($link, "SELECT * FROM guides WHERE id=$id LIMIT 1");
  $row=$res?mysqli_fetch_assoc($res):null;
  if (!$row) return '<div class="card">Nie znaleziono artykułu.</div>';
  return GuideForm($row,'edit',$id);
}

function UsunPoradnik(int $id): string {
  global $link;
  $id=(int)$id;
  mysqli_query($link, "DELETE FROM guides WHERE id=$id LIMIT 1");
  return '<div class="card">Usunięto. <a href="admin.php?action=guide_list">Wróć</a></div>';
}


$action = (string)($_GET['action'] ?? 'list');

if ($action === 'logout') {
 unset($_SESSION['logged']);
 session_destroy();
 header('Location: admin.php');
 exit;
}

if (!is_logged_in()) {
 echo render_admin_layout('Logowanie', FormularzLogowania());
 exit;
}

// logged in
$content = '';

if ($action === 'add') {
  $content = DodajNowaPodstrone();
} elseif ($action === 'edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujPodstrone($id);
} elseif ($action === 'delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunPodstrone($id);

// Kategorie
} elseif ($action === 'cat_list') {
  $content = ListaKategorii();
} elseif ($action === 'cat_add') {
  $content = DodajKategorie();
} elseif ($action === 'cat_edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujKategorie($id);
} elseif ($action === 'cat_delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunKategorie($id);

// Produkty
} elseif ($action === 'prod_list') {
  $content = ListaProduktow();
} elseif ($action === 'prod_add') {
  $content = DodajProdukt();
} elseif ($action === 'prod_edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujProdukt($id);
} elseif ($action === 'prod_delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunProdukt($id);

// Filmy
} elseif ($action === 'videos_list') {
  $content = ListaFilmow();
} elseif ($action === 'videos_add') {
  $content = DodajFilm();
} elseif ($action === 'videos_edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujFilm($id);
} elseif ($action === 'videos_delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunFilm($id);

// FAQ
} elseif ($action === 'faq_list') {
  $content = ListaFAQ();
} elseif ($action === 'faq_add') {
  $content = DodajFAQ();
} elseif ($action === 'faq_edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujFAQ($id);
} elseif ($action === 'faq_delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunFAQ($id);

// Poradnik
} elseif ($action === 'guide_list') {
  $content = ListaPoradnik();
} elseif ($action === 'guide_add') {
  $content = DodajPoradnik();
} elseif ($action === 'guide_edit') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = EdytujPoradnik($id);
} elseif ($action === 'guide_delete') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $content = UsunPoradnik($id);

} else {
  // domyślnie: lista podstron
  $content = ListaPodstron();
}

echo render_admin_layout('Panel', $content);
