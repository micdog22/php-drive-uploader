<?php
require __DIR__ . '/../src/bootstrap.php';
use App\Db;
use App\Auth;

$pdo = Db::pdo($config);
Auth::require_login();
$user = Auth::user($pdo);

$st = $pdo->prepare("SELECT drive_file_id, drive_file_name, size_bytes, created_at FROM files WHERE user_id=? ORDER BY id DESC");
$st->execute([$user['id']]);
$files = $st->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Arquivos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
  <article>
    <h1>Meus arquivos</h1>
    <p><a href="/dashboard.php">Voltar</a></p>
    <table>
      <thead><tr><th>Nome</th><th>Tamanho (bytes)</th><th>Data</th><th>Abrir</th></tr></thead>
      <tbody>
      <?php foreach ($files as $f): ?>
        <tr>
          <td><?=htmlspecialchars($f['drive_file_name'])?></td>
          <td><?=number_format((int)$f['size_bytes'], 0, ',', '.')?></td>
          <td><?=htmlspecialchars($f['created_at'])?></td>
          <td><a target="_blank" href="https://drive.google.com/file/d/<?=urlencode($f['drive_file_id'])?>/view">Abrir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </article>
</main>
</body>
</html>
