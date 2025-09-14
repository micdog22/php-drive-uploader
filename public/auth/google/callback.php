<?php
require __DIR__ . '/../../src/bootstrap.php';

use App\Db;
use App\Auth;
use App\GoogleDriveClient;
use Google\Client as GoogleClient;

$pdo = Db::pdo($config);
Auth::require_login();
$user = Auth::user($pdo);

if (!isset($_GET['code'])) {
  exit('Faltou code');
}

$client = new GoogleClient();
$client->setApplicationName($config['google']['app_name']);
$client->setScopes($config['google']['scopes']);
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->setRedirectUri($config['google']['redirect_uri']);
$client->setAuthConfig($config['google']['credentials_path']);

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (isset($token['error'])) {
  exit('Erro OAuth: ' . htmlspecialchars($token['error']));
}
GoogleDriveClient::storeToken($config, (int)$user['id'], $token);
header('Location: /dashboard.php');
