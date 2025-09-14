<?php
// Maps .env to constants
return [
  'app_env' => getenv('APP_ENV') ?: 'local',
  'app_debug' => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
  'app_url' => getenv('APP_URL') ?: 'http://localhost:8080',
  'db' => [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'database' => getenv('DB_DATABASE') ?: 'php_drive',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
  ],
  'admin_enable_self_register' => filter_var(getenv('ADMIN_ENABLE_SELF_REGISTER') ?: 'false', FILTER_VALIDATE_BOOLEAN),
  'google' => [
    'app_name' => getenv('GOOGLE_APP_NAME') ?: 'PHP Drive Uploader',
    'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: '',
    'scopes' => array_map('trim', explode(',', str_replace(['[',']','"'], '', getenv('GOOGLE_SCOPES') ?: 'https://www.googleapis.com/auth/drive.file'))),
    'credentials_path' => __DIR__ . '/storage/google/credentials.json',
    'tokens_dir' => __DIR__ . '/storage/google/tokens',
  ],
];
