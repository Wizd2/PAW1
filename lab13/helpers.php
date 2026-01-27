<?php
// ==========================================
// ClickClick — helpers.php
// Proste funkcje pomocnicze, zeby nie powtarzac tego samego w wielu plikach.
// Komentarze sa "studenckie" — prosto i na temat.
// ==========================================

// Bezpieczne wypisanie tekstu w HTML (zamiast surowego echo)
function cc_h(string $str): string {
 return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Liczymy cene brutto na podstawie ceny netto i VAT (w %)
// W / cena brutto ma byc liczona w PHP, a nie trzymana w bazie.
function cc_price_brutto(float $netto, int $vat): float {
 return $netto + ($netto * ($vat / 100));
}
