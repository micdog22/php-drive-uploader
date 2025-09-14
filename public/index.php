<?php
require __DIR__ . '/../src/bootstrap.php';

use App\Db;
use App\Auth;

$pdo = Db::pdo($config);
$msg = '';

// Simple rate-limit per IP (memory)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'x';
$_SESSION['rate'][$ip] = $_SESSION['rate'][$ip] ?? ['n'=>0, 't'=>time()];
if (time() - $_SESSION['rate'][$ip]['t'] > 60) { $_SESSION['rate'][$ip] = ['n'=>0,'t'=>time()]; }
if ($_SESSION['rate'][$ip]['n'] > 20) { http_response_code(429); exit('Too many attempts'); }

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(419); exit('CSRF token mismatch');
  }
  $_SESSION['rate'][$ip]['n']++;
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  if (Auth::attempt($pdo, $email, $password)) {
    header('Location: /dashboard.php'); exit;
  } else {
    $msg = 'Credenciais inválidas ou usuário inativo.';
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Login - PHP Drive Uploader</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
  <article>
    <h1>Login</h1>
    <?php if ($msg): ?><mark><?=htmlspecialchars($msg)?></mark><?php endif; ?>
    <form method="post">
      <?=csrf_field()?>
      <label>E-mail
        <input type="email" name="email" required>
      </label>
      <label>Senha
        <input type="password" name="password" required>
      </label>
      <button type="submit">Entrar</button>
    </form>
    <?php if (($config['admin_enable_self_register'] ?? false)): ?>
      <p><a href="/register.php">Criar conta</a></p>
    <?php endif; ?>
  </article>
</main>
</body>
</html>
