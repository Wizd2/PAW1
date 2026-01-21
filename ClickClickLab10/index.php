<?php
// ================================
// ClickClick — v2.0 (Lab10)
// index.php — glowny plik strony
// - trzyma szablon strony (menu, stopka, wyglad)
// - na podstawie parametru idp decyduje co wyswietlic
// - dla kontaktu (Lab8) pokazuje formularz i wysyla maila
// ================================

// ClickClick — v2.0 (Lab10)
// CMS cz.1+cz.2 + Kontakt (wysylka maila)
// Ustawiamy raportowanie bledow (zeby nie wyswietlalo sie za duzo ostrzezen)
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// Dolacz konfiguracje bazy danych
// Dolaczamy cfg.php, bo tam jest polaczenie z baza + login/haslo admina
include_once('cfg.php');
// Dolaczamy contact.php z Lab8 (formularz + wysylanie maila)
include_once('contact.php');
// Lab10: sklep / kategorie (drzewo kategorii)
include_once('sklep.php');

// idp mowi jaka podstrone chcemy (np. switches, faq)
$idp = isset($_GET['idp']) ? $_GET['idp'] : '';
// Proste 'czyszczenie' idp - zeby nikt nie wstrzyknal dziwnych znakow
$idp = preg_replace('/[^a-zA-Z0-9_\-]/', '', $idp);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="Content-Language" content="pl" />
  <meta name="Author" content="Twoje Imię i Nazwisko" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ClickClick — sklep z częściami do klawiatur</title>
  <link rel="stylesheet" href="css/style.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
  <div class="wrapper">
    <!-- Wymagany szkielet statyczny: table / tr / td -->
    <table class="layout">
      <tr>
        <td class="headerCell">
          <div class="brand">
            <div class="brandLeft">
              <div class="logo" aria-hidden="true"></div>
              <div class="brandTitle">
                <h1>ClickClick</h1>
                <p>Custom keyboard parts — switche, keycapy, kable i więcej</p>
              </div>
            </div>

            <div class="brandRight">
              <div class="topActions" aria-label="Szukaj i koszyk">
                <div class="searchWrap">
                  <span class="searchIcon" aria-hidden="true">⌕</span>
                  <input class="search" type="search" placeholder="Szukaj: switche, keycapy, kable…" disabled />
                </div>
                <button class="themeBtn" type="button" onclick="changeBackground()">Zmień tło</button>
                <span class="themeLabel" id="themeLabel" aria-hidden="true"></span>
                <a class="iconBtn" href="#" title="Koszyk (w Lab12)" onclick="return false;">
                  🛒
                  <span class="cartBadge">0</span>
                </a>
              </div>

              <nav class="nav" aria-label="Menu">
                <a class="<?php echo ($idp=='' ? 'active' : ''); ?>" href="index.php">Home</a>
                <a class="<?php echo ($idp=='kategorie' ? 'active' : ''); ?>" href="index.php?idp=kategorie">Kategorie</a>
                <a class="<?php echo ($idp=='switches' ? 'active' : ''); ?>" href="index.php?idp=switches">Switche</a>
                <a class="<?php echo ($idp=='keycaps' ? 'active' : ''); ?>" href="index.php?idp=keycaps">Keycapy</a>
                <a class="<?php echo ($idp=='cables' ? 'active' : ''); ?>" href="index.php?idp=cables">Kable</a>
                <a class="<?php echo ($idp=='guide' ? 'active' : ''); ?>" href="index.php?idp=guide">Poradnik</a>
                <a class="<?php echo ($idp=='faq' ? 'active' : ''); ?>" href="index.php?idp=faq">FAQ</a>
                <a class="<?php echo ($idp=='filmy' ? 'active' : ''); ?>" href="index.php?idp=filmy">Filmy</a>
                <a class="<?php echo ($idp=='contact' ? 'active' : ''); ?>" href="index.php?idp=contact">Kontakt</a>
                <a class="<?php echo ($idp=='about' ? 'active' : ''); ?>" href="index.php?idp=about">O nas</a>
              </nav>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td class="contentCell">
          <?php
            // Lab8: formularz kontaktowy w PHP
// Dla kontaktu robimy osobna obsluge (nie z bazy), bo tu sa akcje POST
            if ($idp === 'contact') {
              $action = isset($_GET['action']) ? $_GET['action'] : '';
              $action = preg_replace('/[^a-zA-Z0-9_\-]/', '', $action);

              if ($action === 'send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                echo WyslijMailKontakt();
              } elseif ($action === 'remind' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                echo PrzypomnijHaslo();
              } else {
                echo PokazKontakt();
              }
            } elseif ($idp === 'kategorie') {
              // Lab10: wyswietlanie drzewa kategorii sklepu
              echo PokazKategorieSklepu();
            } else {
              // Wyswietlanie tresci z bazy danych (Lab6)
              include('showpage.php');
            }
          ?>
        </td>
      </tr>

      <tr>
        <td class="footerCell">
          <div class="footerFlex">
            <small>© ClickClick — v2.0 (Lab10) | <span id="datetime"></span></small>
            <small><code>cfg.php + showpage.php + MySQL (page_list)</code></small>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <script src="js/kolorujtlo.js"></script>
  <script src="js/timedate.js"></script>
  <script src="js/switches-audio.js"></script>
  <script src="js/lab3-jquery.js"></script>

  <?php
    // Identyfikator autora (Lab5/Lab6)
    $nr_indeksu = '175274';
    $nrGrupy = 'ISI-2';
    echo '<div style="text-align:center; margin-top:16px; color: rgba(255,255,255,.65); font-size:12px;">';
    echo 'Autor: [Twoje Imię i Nazwisko] ' . $nr_indeksu . ' grupa ' . $nrGrupy;
    echo '</div>';
  ?>
</body>
</html>
