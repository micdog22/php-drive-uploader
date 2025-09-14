<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Db {
  private static ?PDO $pdo = null;

  public static function pdo(array $cfg): PDO {
    if (self::$pdo) return self::$pdo;
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
      $cfg['db']['host'], $cfg['db']['port'], $cfg['db']['database']);
    try {
      self::$pdo = new PDO($dsn, $cfg['db']['username'], $cfg['db']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
      return self::$pdo;
    } catch (PDOException $e) {
      http_response_code(500);
      exit('DB connection error');
    }
  }
}
