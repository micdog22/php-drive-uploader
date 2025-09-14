<?php
declare(strict_types=1);

namespace App;

use PDO;

class Auth {
  public static function attempt(PDO $pdo, string $email, string $password): bool {
    $st = $pdo->prepare("SELECT id,name,email,password,role,is_active FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $u = $st->fetch();
    if (!$u) return false;
    if (!$u['is_active']) return false;
    if (!password_verify($password, $u['password'])) return false;
    $_SESSION['uid'] = (int)$u['id'];
    $_SESSION['name'] = $u['name'];
    $_SESSION['email'] = $u['email'];
    $_SESSION['role'] = $u['role'];
    return true;
  }

  public static function user(PDO $pdo): ?array {
    if (!isset($_SESSION['uid'])) return null;
    $st = $pdo->prepare("SELECT id,name,email,role,is_active,quota_mb,used_mb FROM users WHERE id=? LIMIT 1");
    $st->execute([$_SESSION['uid']]);
    return $st->fetch() ?: null;
  }

  public static function check_role(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
  }

  public static function require_login(): void {
    if (!isset($_SESSION['uid'])) {
      header('Location: /index.php');
      exit;
    }
  }

  public static function logout(): void {
    session_destroy();
  }
}
