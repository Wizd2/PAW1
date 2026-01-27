<?php
// ================================
// ClickClick — cfg.php
// Ten plik jest dolaczany na poczatku strony.
// - polaczenie z baza MySQL 
// - login/haslo do admina 
// - e-mail admina do kontaktu 
// ================================

// ClickClick — v1.5 
// Konfiguracja polaczenia z baza danych (MySQL)

// Dostosuj wartosci do swojego XAMPP / MySQL
// Zwykle w XAMPP jest user: root i puste haslo, ale czasem bywa inaczej
// Dolaczamy zmienne srodowiskowe (zabezpieczone hasla)
require_once __DIR__ . '/config/env.php';

// Laczymy sie z baza danych (mysqli)
// Jak cos nie dziala: sprawdz czy MySQL w XAMPP jest uruchomiony
$link = @mysqli_connect($cfg_db_host, $cfg_db_user, $cfg_db_pass, $cfg_db_name);

if (!$link) {
 // W lepiej pokazac czytelny komunikat
 die('<div style="padding:12px; border:1px solid rgba(255,255,255,.2); border-radius:12px; background: rgba(255,255,255,.06);">'
. '<b>Blad polaczenia z baza danych.</b><br />'
. 'Sprawdz cfg.php oraz czy MySQL w XAMPP jest uruchomiony.'
. '</div>');
}

// Ustawiamy kodowanie na utf8, zeby polskie znaki nie wariowaly
mysqli_set_charset($link, 'utf8');

// Dane logowania i email sa w config/env.php
