<?php
$configPath = __DIR__ . '/config/google_oauth.json';
if (!file_exists($configPath)) {
  die('Brak konfiguracji Google OAuth');
}
$config = json_decode(file_get_contents($configPath), true);

$query = http_build_query([
  'client_id' => $config['client_id'],
  'redirect_uri' => $config['redirect_uri'],
  'response_type' => 'code',
  'scope' => 'email profile',
  'prompt' => 'select_account'
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $query);
exit;
