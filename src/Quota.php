<?php
declare(strict_types=1);

namespace App;

use PDO;

class Quota {
  public static function canUpload(PDO $pdo, int $userId, int $sizeBytes): bool {
    $st = $pdo->prepare("SELECT quota_mb, used_mb FROM users WHERE id=?");
    $st->execute([$userId]);
    $row = $st->fetch();
    if (!$row) return false;
    $quota = (int)$row['quota_mb'] * 1024 * 1024;
    $used  = (int)$row['used_mb'] * 1024 * 1024;
    return ($used + $sizeBytes) <= $quota;
  }

  public static function addUsage(PDO $pdo, int $userId, int $sizeBytes): void {
    $mb = (int)ceil($sizeBytes / (1024 * 1024));
    $st = $pdo->prepare("UPDATE users SET used_mb = used_mb + ? WHERE id=?");
    $st->execute([$mb, $userId]);
  }
}
