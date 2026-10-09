<?php
require __DIR__ . '/../src/bootstrap.php';
use App\Db;
use App\Auth;
use App\Quota;
use App\GoogleDriveClient;

$pdo = Db::pdo($config);
Auth::require_login();
$user = Auth::user($pdo);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: /dashboard.php'); exit;
}
if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
  exit('Arquivo inválido');
}
$size = (int)$_FILES['file']['size'];
if (!Quota::canUpload($pdo, (int)$user['id'], $size)) {
  exit('Cota excedida');
}
try {
  $gd = new GoogleDriveClient($config, (int)$user['id']);
  $res = $gd->upload($pdo, $user, $_FILES['file']);
  Quota::addUsage($pdo, (int)$user['id'], $size);
  header('Location: /files.php'); exit;
} catch (\Throwable $e) {
  echo "Erro: ".$e->getMessage().". Talvez seja necessário <a href='/auth/google/login.php'>conectar ao Google</a>.";
}
