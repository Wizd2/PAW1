<?php
// forgot_passwd.php - logic for forgot password inside index.php
require_once 'cfg.php';

$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Sprawdzamy czy user istnieje
    $stmt = $link->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        // Generujemy token (32 bajty hex)
        $token = bin2hex(random_bytes(32));
        // Data wygaśnięcia (+1 godzina)
        $expires = date('Y-m-d H:i:s', time() + 3600);
        
        $stmtUpd = $link->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmtUpd->bind_param("sss", $token, $expires, $email);
        $stmtUpd->execute();
        
        // Wysylka linku
        $linkReset = "http://localhost/ClickClick/index.php?idp=reset&token=$token";
        
        $subject = "Zmiana hasla - ClickClick";
        $body = "Witaj,\n\nAby zresetowac haslo, kliknij w link:\n$linkReset\n\nLink wazny przez 1 godzine.";
        
        require_once 'smtp_helper.php';
        if (send_smtp_mail($email, $subject, $body)) {
             $info = '<div style="background:#dcfce7; color:#166534; padding:10px; border-radius:6px; margin-bottom:12px;">Link do resetu został wysłany na Twój email.</div>';
        } else {
             $info = '<div style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px; margin-bottom:12px;">Nie udało się wysłać maila (Błąd SMTP).</div>';
        }
    } else {
        $info = '<div style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px; margin-bottom:12px;">Nie znaleziono takiego adresu e-mail.</div>';
    }
}
?>
<section class="card" style="max-width:400px; margin:0 auto;">
  <h2>Przypomnij hasło</h2>
  <?php echo $info; ?>
  
  <form method="post" action="index.php?idp=forgot">
    <div style="margin-top:12px;">
      <label><b>E-mail</b></label><br>
      <input class="input" name="email" type="email" required>
    </div>
    <div style="margin-top:20px;">
      <button class="btn" type="submit">Wyślij link</button>
    </div>
  </form>
  <div style="margin-top:15px; text-align:center;">
      <a href="index.php?idp=login">Powrót do logowania</a>
   </div>
</section>
