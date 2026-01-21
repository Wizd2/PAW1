<?php
/* ================================
   Lab 4 — Podstawy PHP
   Autor: [Twoje Imię i Nazwisko]
   Nr indeksu: 175274
   Grupa: ISI-2
   ================================ */

echo "<h2>Lab 4 — Podstawy PHP</h2>";

/* --------------------------------
   Dane studenta
--------------------------------- */
$nr_indeksu = "175274";
$nr_grupy = "ISI-2";

echo "Student: <b>Nr indeksu:</b> $nr_indeksu | <b>Grupa:</b> $nr_grupy <br /><br />";

/* --------------------------------
   include() i require_once()
--------------------------------- */
echo "<h3>1) include() i require_once()</h3>";

include_once("test_include.php");
require_once("test_require.php");

echo "<br />";

/* --------------------------------
   Instrukcje warunkowe
--------------------------------- */
echo "<h3>2) Instrukcje warunkowe (if / else / elseif)</h3>";

$a = 10;
$b = 7;

if ($a > $b) {
    echo "Zmienna a jest większa od b <br />";
} elseif ($a == $b) {
    echo "Zmienna a jest równa b <br />";
} else {
    echo "Zmienna a jest mniejsza od b <br />";
}

/* switch */
echo "<h3>Instrukcja switch</h3>";

$day = 3;

switch ($day) {
    case 1:
        echo "Poniedziałek <br />";
        break;
    case 2:
        echo "Wtorek <br />";
        break;
    case 3:
        echo "Środa <br />";
        break;
    default:
        echo "Inny dzień <br />";
}

/* --------------------------------
   Pętle
--------------------------------- */
echo "<h3>3) Pętla for</h3>";

for ($i = 1; $i <= 5; $i++) {
    echo "Iteracja pętli for: $i <br />";
}

echo "<h3>Pętla while</h3>";

$j = 1;
while ($j <= 5) {
    echo "Iteracja pętli while: $j <br />";
    $j++;
}

/* --------------------------------
   Zmienne superglobalne
--------------------------------- */
echo "<h3>4) Zmienne superglobalne</h3>";

/* $_GET */
echo "<h4>\$_GET</h4>";
if (isset($_GET['test'])) {
    echo "Wartość z GET: " . $_GET['test'] . "<br />";
} else {
    echo "Brak parametru GET (np. ?test=123) <br />";
}

/* $_POST */
echo "<h4>\$_POST</h4>";
if (isset($_POST['post_test'])) {
    echo "Wartość z POST: " . $_POST['post_test'] . "<br />";
} else {
    echo "Brak danych POST <br />";
}

/* Formularz POST */
echo '
<form method="post">
    <input type="text" name="post_test" placeholder="Wpisz coś..." />
    <input type="submit" value="Wyślij POST" />
</form>
<br />';

/* $_SESSION */
echo "<h4>\$_SESSION</h4>";
session_start();

if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 1;
} else {
    $_SESSION['counter']++;
}

echo "Licznik sesji: " . $_SESSION['counter'] . "<br />";

?>
