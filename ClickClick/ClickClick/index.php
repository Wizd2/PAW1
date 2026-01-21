<?php
// ================================
// ClickClick — v2.0 
// index.php — glowny plik strony
// - trzyma szablon strony (menu, stopka, wyglad)
// - na podstawie parametru idp decyduje co wyswietlic
// - dla kontaktu pokazuje formularz i wysyla maila
// ================================

// ClickClick — v2.0 
// CMS cz.1+cz.2 + Kontakt (wysylka maila)
// Ustawiamy raportowanie bledow (zeby nie wyswietlalo sie za duzo ostrzezen)
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// : koszyk dziala na sesji, wiec musimy startowac sesje na stronie
// (w panelu admina tez jest sesja, ale tu robimy to dla klienta/sklepu)
if (session_status() === PHP_SESSION_NONE) {
 session_start();
}

// Dolacz konfiguracje bazy danych
// Dolaczamy cfg.php, bo tam jest polaczenie z baza + login/haslo admina
include_once('cfg.php');
// Dolaczamy contact.php z (formularz + wysylanie maila)
include_once('contact.php');
// : sklep / kategorie (drzewo kategorii)
include_once('sklep.php');
// : produkty (lista produktow + VAT)
include_once('products.php');
// : koszyk (SESSION)
include_once('cart.php');

// idp mowi jaka podstrone chcemy (np. switches, faq)
$idp = isset($_GET['idp']) ? $_GET['idp'] : '';
// Proste 'czyszczenie' idp - zeby nikt nie wstrzyknal dziwnych znakow
$idp = preg_replace('/[^a-zA-Z0-9_\-]/', '', $idp);

// Liczymy ile sztuk jest w koszyku (do badge w naglowku)
$cartCount = function_exists('cc_cart_count') ? cc_cart_count() : 0;

// Czy menu kategorii ma byc podswietlone
$catActive = in_array($idp, ['kategorie','switches','keycaps','cables'], true);
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
 <p></p>
 </div>
 </div>

 <div class="brandRight">
 <div class="topActions" aria-label="Szukaj i koszyk">
<button class="themeBtn" type="button" onclick="changeBackground()">Zmień tło</button>
 <span class="themeLabel" id="themeLabel" aria-hidden="true"></span>
 <a class="iconBtn" href="index.php?idp=cart" title="Koszyk">
 🛒
 <span class="cartBadge"><?php echo (int)$cartCount; ?></span>
 </a>
 </div>

 <nav class="nav" aria-label="Menu">
                <a class="<?php echo ($idp=='' ? 'active' : ''); ?>" href="index.php">Home</a>

                <div class="dropdown <?php echo ($catActive ? 'active' : ''); ?>">
                  <button class="dropBtn" type="button" aria-haspopup="true" aria-expanded="false">
                    Kategorie <span aria-hidden="true">▾</span>
                  </button>
                  <div class="dropMenu" role="menu">
                    <a role="menuitem" href="index.php?idp=kategorie">Wszystkie kategorie</a>
                    <div class="dropSep" aria-hidden="true"></div>
                    <a role="menuitem" href="index.php?idp=switches">Switche</a>
                    <a role="menuitem" href="index.php?idp=keycaps">Keycapy</a>
                    <a role="menuitem" href="index.php?idp=cables">Kable</a>
                  </div>
                </div>

                <a class="<?php echo ($idp=='guide' ? 'active' : ''); ?>" href="index.php?idp=guide">Poradnik</a>
                <a class="<?php echo ($idp=='faq' ? 'active' : ''); ?>" href="index.php?idp=faq">FAQ</a>
                <a class="<?php echo ($idp=='filmy' ? 'active' : ''); ?>" href="index.php?idp=filmy">Filmy</a>
                <a class="<?php echo ($idp=='contact' ? 'active' : ''); ?>" href="index.php?idp=contact">Kontakt</a>
                <a class="<?php echo ($idp=='about' ? 'active' : ''); ?>" href="index.php?idp=about">O nas</a>
                <a class="<?php echo ($idp=='cart' ? 'active' : ''); ?>" href="index.php?idp=cart">Koszyk</a>
              </nav>
 </div>
 </div>
 </td>
 </tr>

 <tr>
 <td class="contentCell">
 <?php
 // : formularz kontaktowy w PHP
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
 // : wyswietlanie drzewa kategorii sklepu
 echo PokazKategorieSklepu();
 } elseif ($idp === 'switches' || $idp === 'keycaps' || $idp === 'cables') {
 // : produkty z bazy (zamiast "statycznych" kart w tresci strony)
 echo PokazProduktySklepu();
 } elseif ($idp === 'filmy') {
  echo PokazFilmy();
} elseif ($idp === 'faq') {
  echo PokazFAQ();
} elseif ($idp === 'guide') {
  echo PokazPoradnik();
} elseif ($idp === 'cart') {
 // : koszyk oparty o sesje
 echo PokazKoszyk();
 } else {
 // Wyswietlanie tresci z bazy danych 
 include('showpage.php');
 }
 ?>
 </td>
 </tr>

 <tr>
 <td class="footerCell">
 <div class="footerFlex">
 <small>© ClickClick — v2.2 | <span id="datetime"></span></small>
 <small><code>cfg.php + showpage.php + MySQL (page_list)</code></small>
 </div>
 </td>
 </tr>
 </table>
 </div>

 <script src="js/kolorujtlo.js"></script>
 <script src="js/timedate.js"></script>
 <script src="js/switches-audio.js"></script>
 <script src="js/-jquery.js"></script>

 <?php
 // Identyfikator autora 
 $nr_indeksu = '175274';
 $nrGrupy = 'ISI-2';
 echo '<div style="text-align:center; margin-top:16px; color: rgba(255,255,255,.65); font-size:12px;">';
 echo 'Autor: Illia Matuznyi '. $nr_indeksu. ' grupa '. $nrGrupy;
 echo '</div>';
 ?>
</body>
</html>
