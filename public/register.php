<?php
require __DIR__ . '/../src/bootstrap.php';

use App\Db;

if (!($config['admin_enable_self_register'] ?? false)) { http_response_code(404); exit('Registro desativado'); }

$pdo = Db::pdo($config);
$msg = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');
  if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
    $st = $pdo->prepare("INSERT INTO users (name,email,password,role,is_active,quota_mb,used_mb) VALUES (?,?,?,?,1,100,0)");
    try {
      $st->execute([$name,$email,password_hash($password, PASSWORD_DEFAULT),'user']);
      header('Location: /index.php'); exit;
    } catch (\PDOException $e) {
      $msg = 'E-mail já cadastrado.';
    }
  } else {
    $msg = 'Dados inválidos.';
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Registrar - PHP Drive Uploader</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
  <article>
    <h1>Criar conta</h1>
    <?php if ($msg): ?><mark><?=htmlspecialchars($msg)?></mark><?php endif; ?>
    <form method="post">
      <?=csrf_field()?>
      <label>Nome <input name="name" required></label>
      <label>E-mail <input type="email" name="email" required></label>
      <label>Senha <input type="password" name="password" required minlength="6"></label>
      <button type="submit">Registrar</button>
    </form>
    <p><a href="/index.php">Voltar</a></p>
  </article>
</main>
</body>
</html>
