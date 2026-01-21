<?php
// ClickClick — v1.4 (Lab5)
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$idp = isset($_GET['idp']) ? $_GET['idp'] : '';
$idp = preg_replace('/[^a-zA-Z0-9_\-]/', '', $idp);

// Mapowanie podstron (dynamiczne ladowanie tresci)
if ($idp == '') $strona = 'html/glowna.html';
if ($idp == 'catalog') $strona = 'html/catalog.html';
if ($idp == 'switches') $strona = 'html/switches.html';
if ($idp == 'keycaps') $strona = 'html/keycaps.html';
if ($idp == 'cables') $strona = 'html/cables.html';
if ($idp == 'guide') $strona = 'html/guide.html';
if ($idp == 'faq') $strona = 'html/faq.html';
if ($idp == 'filmy') $strona = 'html/filmy.html';
if ($idp == 'contact') $strona = 'html/contact.html';
if ($idp == 'about') $strona = 'html/about.html';

// Zabezpieczenie: sprawdz czy plik istnieje
if (!isset($strona) || !file_exists($strona)) {
  $strona = 'html/glowna.html';
  $idp = '';
  $not_found = true;
}
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
                <a class="<?php echo ($idp=='catalog' ? 'active' : ''); ?>" href="index.php?idp=catalog">Sklep</a>
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
            if (isset($not_found) && $not_found === true) {
              echo '<div class="card" style="margin-bottom:12px;">';
              echo '<b>Uwaga:</b> podstrona nie istnieje — pokazano stronę główną.';
              echo '</div>';
            }
            include($strona);
          ?>
        </td>
      </tr>

      <tr>
        <td class="footerCell">
          <div class="footerFlex">
            <small>© ClickClick — v1.4 (Lab5) | <span id="datetime"></span></small>
            <small><code>index.php + $_GET + include()</code></small>
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
    // Identyfikator autora (Lab5)
    $nr_indeksu = '175274';
    $nrGrupy = 'ISI-2';
    echo '<div style="text-align:center; margin-top:16px; color: rgba(255,255,255,.65); font-size:12px;">';
    echo 'Autor: [Twoje Imię i Nazwisko] ' . $nr_indeksu . ' grupa ' . $nrGrupy;
    echo '</div>';
  ?>
</body>
</html>
