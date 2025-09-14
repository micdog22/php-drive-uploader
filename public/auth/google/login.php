<?php
require __DIR__ . '/../../src/bootstrap.php';
use App\GoogleDriveClient;
use App\Db;
use App\Auth;

$pdo = Db::pdo($config);
Auth::require_login();

$url = GoogleDriveClient::oauthUrl($config);
header('Location: ' . $url);
