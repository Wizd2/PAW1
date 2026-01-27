<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Przypomnij hasło</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

 <div class="wrapper">
 <table class="layout">
 <tr>
 <td class="headerCell">
 <div class="brand">
 <div class="brandLeft">
 <div class="logo" aria-hidden="true"></div>
 <div class="brandTitle">
 <h1>ClickClick — Admin</h1>
 <p>CMS v2.1 </p>
 </div>
 </div>
 '. . '
 </div>
 </td>
 </tr>
 <tr>
 <td class="contentCell">
<?php
 session_start();
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
         $headers = "From: no-reply@clickclick.local\r\n";
         $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
         
         @mail($email, $subject, $body, $headers);
         
         $info = '<div style="background:#dcfce7; color:#166534; padding:10px; border-radius:6px;">Link do resetu został wysłany na Twój email.</div>';
     } else {
         $info = '<div style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px;">Nie znaleziono takiego adresu e-mail.</div>';
     }
 }
 ?>
 <section class="card" style="max-width:400px; margin:0 auto;">
   <h2>Przypomnij hasło</h2>
   <?php echo $info; ?>
   
   <form method="post" action="old_forgot.php">
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
 </td>
 </tr>
 <tr>
 <td class="footerCell">
 <div class="footerFlex">
 <small>Panel administracyjny — (Produkty + VAT)</small>
 
 <small><a href="../index.php">Powrót do sklepu</a></small>
 </div>
 </td>
 </tr>
 </table>
 </div>
 
</body>
</html>
