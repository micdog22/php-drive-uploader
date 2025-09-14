<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$config = require dirname(__DIR__) . '/config.php';

session_name('php_drive');
@session_start();

// Basic CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="'.htmlspecialchars($_SESSION['csrf'] ?? '').'">';
}
function csrf_check(): void {
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(419);
    exit('CSRF token mismatch');
  }
}
