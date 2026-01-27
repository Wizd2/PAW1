<?php
require_once 'cfg.php';

$email = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.php?idp=register');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $link->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $hash);

if ($stmt->execute()) {
    header('Location: index.php?idp=login');
} else {
    header('Location: index.php?idp=register');
}
exit;
