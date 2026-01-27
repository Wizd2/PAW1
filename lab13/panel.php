<?php
// panel.php - User Panel Logic
?>
<section class="card" style="max-width:520px; margin:0 auto;">
  <h2>Panel użytkownika</h2>

<?php if (isset($_SESSION['user_id'])): ?>

  <!-- Removed "Jesteś zalogowany" text as requested -->
  <!-- Removed "Witaj" text as requested -->

  <div style="margin-top:20px;">
    <!-- Cleaned up Logout button -->
    <a class="btn" href="index.php?idp=my_orders" style="margin-right:10px;">Moje zamówienia</a>
    <a class="btn danger" href="logout.php">Wyloguj</a>
  </div>

<?php else: ?>

  <!-- Removed "Wybierz jedną z dostępnych opcji." text as requested -->

  <div style="margin-top:12px;">
    <a class="btn" href="index.php?idp=login">Logowanie</a>
  </div>

  <div style="margin-top:12px;">
    <a class="btn" href="index.php?idp=register">Rejestracja</a>
  </div>

  <div style="margin-top:12px;">
    <a class="btn secondary" href="index.php?idp=forgot">Przypomnij hasło</a>
  </div>

<?php endif; ?>

</section>
