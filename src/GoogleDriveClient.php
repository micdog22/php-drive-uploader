<?php
declare(strict_types=1);

namespace App;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use PDO;

class GoogleDriveClient {
  private Drive $service;
  private string $tokensDir;
  private int $userId;

  public function __construct(array $cfg, int $userId) {
    $client = new GoogleClient();
    $client->setApplicationName($cfg['google']['app_name']);
    $client->setScopes($cfg['google']['scopes']);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setRedirectUri($cfg['google']['redirect_uri']);
    $client->setAuthConfig($cfg['google']['credentials_path']);

    $this->tokensDir = $cfg['google']['tokens_dir'];
    $this->userId = $userId;
    if (!is_dir($this->tokensDir)) {
      @mkdir($this->tokensDir, 0775, true);
    }
    $tokenFile = $this->tokensDir . '/' . $userId . '.json';
    if (is_file($tokenFile)) {
      $accessToken = json_decode(file_get_contents($tokenFile), true);
      $client->setAccessToken($accessToken);
      if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($tokenFile, json_encode($client->getAccessToken()));
      }
    } else {
      // Sem token => o caller deve redirecionar para OAuth
      throw new \RuntimeException('Falta token. Faça login no Google.');
    }

    $this->service = new Drive($client);
  }

  public static function oauthUrl(array $cfg): string {
    $client = new GoogleClient();
    $client->setApplicationName($cfg['google']['app_name']);
    $client->setScopes($cfg['google']['scopes']);
    $client->setAccessType('offline');
    $client->setPrompt('consent');
    $client->setRedirectUri($cfg['google']['redirect_uri']);
    $client->setAuthConfig($cfg['google']['credentials_path']);
    return $client->createAuthUrl();
  }

  public static function storeToken(array $cfg, int $userId, array $token): void {
    $dir = $cfg['google']['tokens_dir'];
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    file_put_contents($dir . '/' . $userId . '.json', json_encode($token));
  }

  private function ensureUserFolder(string $userEmail): string {
    // procura pasta "user: {email}" na raiz
    $q = sprintf("name = 'user: %s' and mimeType = 'application/vnd.google-apps.folder' and trashed = false", addslashes($userEmail));
    $res = $this->service->files->listFiles([
      'q' => $q,
      'fields' => 'files(id, name)'
    ]);
    if ($res->files && count($res->files) > 0) {
      return $res->files[0]->id;
    }
    $meta = new DriveFile([
      'name' => "user: {$userEmail}",
      'mimeType' => 'application/vnd.google-apps.folder'
    ]);
    $created = $this->service->files->create($meta, ['fields' => 'id']);
    return $created->id;
  }

  public function upload(PDO $pdo, array $user, array $file): array {
    $folderId = $this->ensureUserFolder($user['email']);
    $driveFile = new DriveFile([
      'name' => $file['name'],
      'parents' => [$folderId]
    ]);
    $created = $this->service->files->create($driveFile, [
      'data' => file_get_contents($file['tmp_name']),
      'mimeType' => $file['type'] ?: 'application/octet-stream',
      'uploadType' => 'multipart',
      'fields' => 'id, name, size, webViewLink, webContentLink'
    ]);
    $size = (int)($created->size ?? 0);
    // registra
    $st = $pdo->prepare("INSERT INTO files (user_id, drive_file_id, drive_file_name, size_bytes) VALUES (?,?,?,?)");
    $st->execute([$user['id'], $created->id, $created->name, $size]);
    return [
      'id' => $created->id,
      'name' => $created->name,
      'size' => $size,
      'webViewLink' => $created->webViewLink ?? null,
      'webContentLink' => $created->webContentLink ?? null,
    ];
  }
}
