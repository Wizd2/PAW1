<?php
session_start();
require_once 'cfg.php';

$email = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $link->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    header('Location: index.php?idp=auth');
    exit;
}

header('Location: index.php?idp=login');
exit;
