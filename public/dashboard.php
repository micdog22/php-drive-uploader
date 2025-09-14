<?php
require __DIR__ . '/../src/bootstrap.php';
use App\Db;
use App\Auth;
use App\Quota;

$pdo = Db::pdo($config);
Auth::require_login();
$user = Auth::user($pdo);

?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
  <article>
    <header>
      <h1>Olá, <?=htmlspecialchars($user['name'])?></h1>
      <nav><a href="/logout.php">Sair</a></nav>
    </header>

    <p><strong>Cota:</strong> <?=$user['used_mb']?> / <?=$user['quota_mb']?> MB</p>

    <section>
      <h3>Conectar ao Google Drive</h3>
      <p>Se ainda não conectou, faça o OAuth.</p>
      <a href="/auth/google/login.php">Conectar/Verificar Google</a>
    </section>

    <section>
      <h3>Upload de arquivo</h3>
      <form action="/upload.php" method="post" enctype="multipart/form-data">
        <?=csrf_field()?>
        <input type="file" name="file" required>
        <button type="submit">Enviar</button>
      </form>
    </section>

    <section>
      <h3>Meus arquivos</h3>
      <a href="/files.php">Listar</a>
    </section>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <section>
      <h3>Admin</h3>
      <a href="/admin.php">Ir ao painel admin</a>
    </section>
    <?php endif; ?>
  </article>
</main>
</body>
</html>
