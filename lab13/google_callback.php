<?php
session_start();
require_once 'cfg.php';

$config = json_decode(file_get_contents(__DIR__ . '/config/google_oauth.json'), true);

$code = $_GET['code'] ?? null;
if (!$code) {
    header('Location: index.php?idp=login');
    exit;
}

$tokenResponse = file_get_contents(
    'https://oauth2.googleapis.com/token',
    false,
    stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'grant_type' => 'authorization_code'
            ])
        ]
    ])
);

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? null;
if (!$accessToken) {
    header('Location: index.php?idp=login');
    exit;
}

$userInfo = json_decode(
    file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken),
    true
);

$email = $userInfo['email'];
$googleId = $userInfo['id'];

$stmt = $link->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    $stmt = $link->prepare(
        "INSERT INTO users (email, password, google_id) VALUES (?, '', ?)"
    );
    $stmt->bind_param("ss", $email, $googleId);
    $stmt->execute();
    $userId = $stmt->insert_id;
} else {
    $userId = $user['id'];
}

$_SESSION['user_id'] = $userId;
header('Location: index.php?idp=auth');
exit;
