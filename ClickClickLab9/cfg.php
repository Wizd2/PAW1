<?php
// ================================
// ClickClick — cfg.php
// Ten plik jest dolaczany na poczatku strony.
// - polaczenie z baza MySQL (Lab6)
// - login/haslo do admina (Lab7)
// - e-mail admina do kontaktu (Lab8)
// ================================

// ClickClick — v1.5 (Lab6)
// Konfiguracja polaczenia z baza danych (MySQL)

// Dostosuj wartosci do swojego XAMPP / MySQL
// Zwykle w XAMPP jest user: root i puste haslo, ale czasem bywa inaczej
$cfg_db_host = 'localhost';
$cfg_db_user = 'root';
$cfg_db_pass = '';      // w wielu instalacjach XAMPP haslo jest puste
$cfg_db_name = 'moja_strona';

// Laczymy sie z baza danych (mysqli)
// Jak cos nie dziala: sprawdz czy MySQL w XAMPP jest uruchomiony
$link = @mysqli_connect($cfg_db_host, $cfg_db_user, $cfg_db_pass, $cfg_db_name);

if (!$link) {
  // W Lab6 lepiej pokazac czytelny komunikat
  die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
    . '<b>Blad polaczenia z baza danych.</b><br />'
    . 'Sprawdz cfg.php oraz czy MySQL w XAMPP jest uruchomiony.'
    . '</div>');
}

// Ustawiamy kodowanie na utf8, zeby polskie znaki nie wariowaly
mysqli_set_charset($link, 'utf8');

// ClickClick — v1.6 (Lab7)
// Dane logowania do panelu administracyjnego (zmien je!)
// To jest proste logowanie na potrzeby laboratorium
// Wymagane w Lab7: zmienne $login i $pass w cfg.php
$login = 'admin';
$pass  = 'admin123';

// ClickClick — v1.7 (Lab8)
// Adres e-mail do odbierania wiadomosci z formularza kontaktowego
// Tu wpisz swoj prawdziwy e-mail, wtedy formularz bedzie mial sens
// (zmien na swoj prawdziwy e-mail)
$admin_email = 'twoj_email@example.com';
