<?php
require_once 'cfg.php';

$email = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.php?idp=register');
    exit;
}

$stmt = $link->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    // E-mail zajęty
    header('Location: index.php?idp=register&error=exists');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$code = sprintf("%06d", mt_rand(100000, 999999));

$stmt = $link->prepare("INSERT INTO users (email, password, verification_code, is_verified) VALUES (?, ?, ?, 0)");
$stmt->bind_param("sss", $email, $hash, $code);

if ($stmt->execute()) {
    $newUserId = $stmt->insert_id;
    $_SESSION['verify_user_id'] = $newUserId;
    
    // Wysyłanie maila z kodem
    $subject = "Kod weryfikacyjny - ClickClick";
    $body = "Witaj, \n\nTwój kod weryfikacyjny to: $code\n\nWpisz go na stronie, aby dokończyć rejestrację.";
    
    // Używamy SMTP Helpera
    require_once 'smtp_helper.php';
    send_smtp_mail($email, $subject, $body);
    
    header('Location: index.php?idp=verify');
} else {
    header('Location: index.php?idp=register&error=db');
}
exit;
