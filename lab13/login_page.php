<?php
// login_page.php - Login form logic
?>
<section class="card" style="max-width:520px; margin:0 auto;">
  <h2>Logowanie</h2>

  <?php if (isset($_GET['error'])): ?>
    <div style="
      margin-bottom:12px;
      padding:10px;
      background:#ffeaea;
      color:#a10000;
      border-radius:6px;
      font-size:14px;
      border: 1px solid #fecaca;
    ">
      Nieprawidłowy login lub hasło
    </div>
  <?php endif; ?>

  <form method="post" action="login_action.php">
    <div style="margin-top:12px;">
      <label><b>E-mail</b></label><br>
      <input class="input" name="login" type="email" required>
    </div>

    <div style="margin-top:12px;">
      <label><b>Hasło</b></label><br>
      <input class="input" name="password" type="password" required>
    </div>

    <div style="margin-top:20px;">
      <button class="btn" type="submit">Zaloguj</button>
    </div>
    
    <!-- Google Login Button -->
    <div style="margin-top: 15px; text-align: center;">
        <a href="google_login.php" class="btn" style="background: #db4437; width: 100%; display: block; text-decoration: none;">
           G Zaloguj przez Google
        </a>
    </div>
  </form>

  <div style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:15px; text-align:center;">
      <a href="index.php?idp=forgot" style="font-size:0.9em; opacity:0.8; margin-right:15px;">Zapomniałeś hasła?</a>
      <a href="index.php?idp=register" style="font-size:0.9em; opacity:0.8;">Rejestracja</a>
  </div>

  <!-- Google Login (placeholder link since logic is elsewhere or unused) -->

</section>
