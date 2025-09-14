<?php
require __DIR__ . '/../src/bootstrap.php';
use App\Db;
use App\Auth;

$pdo = Db::pdo($config);
Auth::require_login();
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); exit('Acesso negado'); }

// ações
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) { http_response_code(419); exit('CSRF'); }
  $action = $_POST['action'] ?? '';
  $uid = (int)($_POST['uid'] ?? 0);
  if ($action === 'toggle') {
    $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$uid]);
  } elseif ($action === 'role') {
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$uid]);
  } elseif ($action === 'quota') {
    $q = max(1, (int)($_POST['quota_mb'] ?? 100));
    $pdo->prepare("UPDATE users SET quota_mb=? WHERE id=?")->execute([$q,$uid]);
  }
  header('Location: /admin.php'); exit;
}

$users = $pdo->query("SELECT id,name,email,role,is_active,quota_mb,used_mb FROM users ORDER BY id DESC")->fetchAll();
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
  <article>
    <h1>Admin</h1>
    <p><a href="/dashboard.php">Voltar</a></p>
    <table>
      <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Role</th><th>Status</th><th>Cota</th><th>Uso</th><th>Ações</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?=$u['id']?></td>
            <td><?=htmlspecialchars($u['name'])?></td>
            <td><?=htmlspecialchars($u['email'])?></td>
            <td><?=$u['role']?></td>
            <td><?=$u['is_active'] ? 'Ativo' : 'Inativo'?></td>
            <td><?=$u['quota_mb']?> MB</td>
            <td><?=$u['used_mb']?> MB</td>
            <td>
              <form method="post" style="display:inline;">
                <?=csrf_field()?>
                <input type="hidden" name="uid" value="<?=$u['id']?>">
                <button name="action" value="toggle"><?=$u['is_active']?'Inativar':'Ativar'?></button>
              </form>
              <form method="post" style="display:inline;">
                <?=csrf_field()?>
                <input type="hidden" name="uid" value="<?=$u['id']?>">
                <select name="role">
                  <option value="user" <?=$u['role']==='user'?'selected':''?>>user</option>
                  <option value="admin" <?=$u['role']==='admin'?'selected':''?>>admin</option>
                </select>
                <button name="action" value="role">Trocar</button>
              </form>
              <form method="post" style="display:inline;">
                <?=csrf_field()?>
                <input type="hidden" name="uid" value="<?=$u['id']?>">
                <input type="number" name="quota_mb" min="1" value="<?=$u['quota_mb']?>" style="width:90px">
                <button name="action" value="quota">Salvar cota</button>
              </form>
              <a href="/impersonate.php?uid=<?=$u['id']?>">Ver área</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </article>
</main>
</body>
</html>
