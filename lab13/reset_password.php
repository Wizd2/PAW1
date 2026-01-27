<?php
// reset_password.php - formularz ustawiania nowego hasła
$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$showForm = true;

if (empty($token)) {
    $error = 'Brak tokenu resetującego.';
    $showForm = false;
}

if ($showForm) {
    // Sprawdzamy token w bazie i czy nie wygasł
    // Format daty w SQL: YYYY-MM-DD HH:MM:SS
    $stmt = $link->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if (!$user) {
        $error = 'Link jest nieprawidłowy lub wygasł.';
        $showForm = false;
    }
}

if ($showForm && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['pass1'] ?? '';
    $pass2 = $_POST['pass2'] ?? '';

    if (strlen($pass1) < 4) {
        $error = 'Hasło jest za krótkie.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Hasła nie są identyczne.';
    } else {
        // Zmiana hasła
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $stmtUpd = $link->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmtUpd->bind_param("si", $hash, $user['id']);
        if ($stmtUpd->execute()) {
             $success = 'Hasło zostało zmienione. Możesz się zalogować.';
             $showForm = false;
        } else {
             $error = 'Wystąpił błąd bazy danych.';
        }
    }
}
?>

<section class="card" style="max-width:520px; margin:0 auto;">
  <h2>Ustaw nowe hasło</h2>

  <?php if ($error): ?>
    <div style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px; margin-bottom:12px;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div style="background:#dcfce7; color:#166534; padding:10px; border-radius:6px; margin-bottom:12px;">
      <?php echo htmlspecialchars($success); ?>
    </div>
    <div style="margin-top:12px;">
       <a class="btn" href="index.php?idp=login">Przejdź do logowania</a>
    </div>
  <?php endif; ?>

  <?php if ($showForm): ?>
  <form method="post" action="index.php?idp=reset&token=<?php echo htmlspecialchars($token); ?>">
    <div style="margin-top:12px;">
      <label><b>Nowe hasło</b></label><br>
      <input class="input" name="pass1" type="password" required>
    </div>

    <div style="margin-top:12px;">
      <label><b>Powtórz hasło</b></label><br>
      <input class="input" name="pass2" type="password" required>
    </div>

    <div style="margin-top:20px;">
      <button class="btn" type="submit">Zmień hasło</button>
    </div>
  </form>
  <?php endif; ?>
</section>
