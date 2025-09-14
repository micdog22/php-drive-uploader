<?php
declare(strict_types=1);

namespace App;

class Helpers {
  public static function redirect(string $to): never {
    header("Location: {$to}");
    exit;
  }
  public static function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
  }
  public static function guard_csrf(): void {
    if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
      http_response_code(419);
      exit('CSRF token mismatch');
    }
  }
}
