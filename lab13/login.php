<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Logowanie</title>
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
 <form method="post" action="login_action.php" class="form">
   <h2 style="margin-bottom:20px;">Logowanie</h2>
   
   <?php if (isset($_GET['error'])): ?>
       <div class="error" style="background:#ffeaea; color:#a10000; padding:10px; border-radius:6px; margin-bottom:15px; border:1px solid #fecaca;">
          Nieprawidłowy login lub hasło
       </div>
   <?php endif; ?>

   <div class="inputGroup">
     <label>E-mail</label>
     <input type="text" name="login" class="input" required />
   </div>
   <div class="inputGroup">
     <label>Hasło</label>
     <input type="password" name="password" class="input" required />
   </div>
   <div style="margin-top:20px;">
     <button type="submit" class="btn">Zaloguj się</button>
   </div>
   <div style="margin-top:10px; text-align:center;">
       <a href="../index.php?idp=forgot" style="font-size:0.9em; opacity:0.8;">Zapomniałeś hasła?</a>
   </div>
 </form>
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
