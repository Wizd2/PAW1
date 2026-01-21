<?php
// ClickClick — v1.5 (Lab6)
// Konfiguracja polaczenia z baza danych (MySQL)

// Dostosuj wartosci do swojego XAMPP / MySQL
$cfg_db_host = 'localhost';
$cfg_db_user = 'root';
$cfg_db_pass = '';      // w wielu instalacjach XAMPP haslo jest puste
$cfg_db_name = 'moja_strona';

// Polaczenie (mysqli)
$link = @mysqli_connect($cfg_db_host, $cfg_db_user, $cfg_db_pass, $cfg_db_name);

if (!$link) {
  // W Lab6 lepiej pokazac czytelny komunikat
  die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
    . '<b>Blad polaczenia z baza danych.</b><br />'
    . 'Sprawdz cfg.php oraz czy MySQL w XAMPP jest uruchomiony.'
    . '</div>');
}

// Ustaw kodowanie
mysqli_set_charset($link, 'utf8');

// ClickClick — v1.6 (Lab7)
// Dane logowania do panelu administracyjnego (zmien je!)
// Wymagane w Lab7: zmienne $login i $pass w cfg.php
$login = 'admin';
$pass  = 'admin123';
