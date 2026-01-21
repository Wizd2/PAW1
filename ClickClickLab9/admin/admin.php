<?php
// ================================
// ClickClick — admin/admin.php (Lab7)
// Prosty panel CMS:
// - logowanie na sesji
// - lista podstron (SELECT)
// - dodawanie (INSERT)
// - edycja (UPDATE LIMIT 1)
// - usuwanie (DELETE LIMIT 1)
// Wszystko pracuje na tabeli page_list
// ================================

// ClickClick — v1.6 (Lab7)
// Prosty CMS: logowanie + CRUD dla tabeli page_list

// Start sesji - dzieki temu pamietamy czy admin jest zalogowany
session_start();

// Dolaczamy cfg.php, bo tam jest polaczenie z baza ($link) + login/haslo
require_once(__DIR__ . '/../cfg.php');

// ---------------------------
// Helpers
// ---------------------------

// h() - krotka funkcja do bezpiecznego wyswietlania tekstu w HTML
function h($str) {
  return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
        <a href="admin.php?action=logout">Wyloguj</a>
      </nav>';
  }

  return '<!DOCTYPE html>
  <html lang="pl">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ClickClick Admin — ' . h($title) . '</title>
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
                  <p>CMS v1.6 (Lab7)</p>
                </div>
              </div>
              ' . $nav . '
            </div>
          </td>
        </tr>
        <tr>
          <td class="contentCell">
            ' . $contentHtml . '
          </td>
        </tr>
        <tr>
          <td class="footerCell">
            <div class="footerFlex">
              <small>Panel administracyjny — Lab7</small>
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
// Lab7: wymagane metody
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
  $html .= '<section class="card" style="max-width:520px; margin:0 auto;">
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

      $rows .= '<tr>'
        . '<td>' . $id . '</td>'
        . '<td><b>' . $title . '</b><div style="margin-top:6px;">' . $badge . '</div></td>'
        . '<td style="white-space:nowrap;">'
        . '<a class="btn" style="padding:8px 10px;" href="admin.php?action=edit&id=' . $id . '">Edytuj</a> '
        . '<a class="btn" style="padding:8px 10px; border-color: rgba(239,68,68,.55); background: rgba(239,68,68,.16);" '
        . 'href="admin.php?action=delete&id=' . $id . '" onclick="return confirm(\'Usunąć podstronę?\');">Usuń</a>'
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
        ' . $rows . '
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
           . '<b>Błąd zapisu:</b> ' . h(mysqli_error($link)) . '</div>';
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

  return $msg . '<section class="card">
    <h2>Edytuj podstronę (ID: ' . $id . ')</h2>
    <p>Edytujesz rekord w tabeli <b>page_list</b>. Pamiętaj o poprawnym HTML w treści.</p>
    <form method="post" action="admin.php?action=edit&id=' . $id . '">
      <div style="margin-top:12px;">
        <label for="page_title"><b>Tytuł / alias (page_title)</b></label><br />
        <input class="input" id="page_title" name="page_title" type="text" value="' . $title . '" required />
      </div>
      <div style="margin-top:12px;">
        <label for="page_content"><b>Treść (page_content)</b></label><br />
        <textarea id="page_content" name="page_content">' . $content . '</textarea>
      </div>
      <div style="margin-top:12px;">
        <label><input type="checkbox" name="status" value="1" ' . $checked . ' /> Aktywna</label>
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
           . '<b>Dodano.</b> Nowa podstrona została utworzona (ID: ' . $newId . ').</div>';

      // Wyczyść formularz po dodaniu
      $titleVal = '';
      $contentVal = '';
      $statusChecked = 'checked';
    } else {
      $msg = '<div class="card" style="border-color: rgba(239,68,68,.45); background: rgba(239,68,68,.08); margin-bottom: 12px;">'
           . '<b>Błąd dodawania:</b> ' . h(mysqli_error($link)) . '</div>';
    }
  }

  return $msg . '<section class="card">
    <h2>Dodaj nową podstronę</h2>
    <p>Tworzysz nowy rekord w tabeli <b>page_list</b> (INSERT).</p>
    <form method="post" action="admin.php?action=add">
      <div style="margin-top:12px;">
        <label for="page_title"><b>Tytuł / alias (page_title)</b></label><br />
        <input class="input" id="page_title" name="page_title" type="text" value="' . h($titleVal) . '" placeholder="np. nowa_strona" required />
      </div>
      <div style="margin-top:12px;">
        <label for="page_content"><b>Treść (page_content)</b></label><br />
        <textarea id="page_content" name="page_content" placeholder="Wklej HTML treści...">' . h($contentVal) . '</textarea>
      </div>
      <div style="margin-top:12px;">
        <label><input type="checkbox" name="status" value="1" ' . $statusChecked . ' /> Aktywna</label>
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
    . '<b>Błąd usuwania:</b> ' . h(mysqli_error($link)) . '</div>'
    . '<div style="margin-top:12px;"><a class="btn" href="admin.php">Powrót do listy</a></div>';
}

// ---------------------------
// Routing
// ---------------------------

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
} else {
  $content = ListaPodstron();
}

echo render_admin_layout('Panel', $content);
