<?php
// verify.php - ввод кода
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    // Проверка идет по сессии (мы знаем кто регистрируется)
    // Либо, если сессия истекла, пользователю придется войти (но он не может войти без верификации).
    // Поэтому лучше хранить ID пользователя в сессии `verify_user_id` после регистрации
    
    // В register_action.php мы запишем $_SESSION['verify_user_id'] = $newUserId;

    if (empty($_SESSION['verify_user_id'])) {
        header("Location: index.php?idp=login");
        exit;
    }

    $uid = (int)$_SESSION['verify_user_id'];
    
    $stmt = $link->prepare("SELECT id, verification_code FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        if ($user['verification_code'] === $code) {
             // OK
             $stmtUpd = $link->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
             $stmtUpd->bind_param("i", $uid);
             $stmtUpd->execute();
             
             unset($_SESSION['verify_user_id']);
             $_SESSION['user_id'] = $uid; // Авторизуем сразу
             
             // Redirect
             header("Location: index.php?idp=auth"); // или на профиль
             exit;
        } else {
             $error = 'Nieprawidłowy kod weryfikacyjny.';
        }
    } else {
         $error = 'Błąd użytkownika. Spróbuj zarejestrować się ponownie.';
    }
}
?>

<section class="card" style="max-width:520px; margin:0 auto;">
  <h2>Weryfikacja konta</h2>
  <p>Na Twój adres e-mail został wysłany 6-cyfrowy kod. Wpisz go poniżej.</p>

  <?php if ($error): ?>
    <div style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px; margin-bottom:12px;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <form method="post" action="index.php?idp=verify">
    <div style="margin-top:12px;">
      <label><b>Kod weryfikacyjny</b></label><br>
      <input class="input" name="code" type="text" placeholder="######" maxlength="6" required style="font-size:18px; letter-spacing:4px; text-align:center;">
    </div>

    <div style="margin-top:20px;">
      <button class="btn" type="submit">Zatwierdź</button>
    </div>
  </form>
</section>
