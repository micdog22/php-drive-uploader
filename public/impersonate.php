<?php
require __DIR__ . '/../src/bootstrap.php';
use App\Db;
use App\Auth;

$pdo = Db::pdo($config);
Auth::require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('Acesso negado'); }

$uid = (int)($_GET['uid'] ?? 0);
if ($uid > 0) {
  // guarda admin uid para voltar
  $_SESSION['impersonate_admin'] = $_SESSION['uid'];
  $_SESSION['uid'] = $uid;
  header('Location: /dashboard.php'); exit;
}
echo 'Usuário inválido';
