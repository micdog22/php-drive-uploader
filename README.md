# PHP Drive Uploader (Vanilla PHP)

Sistema multiusuário com autenticação, papéis (admin/usuário), cotas, e upload direto para Google Drive (cada usuário em sua própria pasta).

## Recursos
- Login + senha (sessions, senha com `password_hash`).
- Papéis: **admin** pode ver e gerenciar cotas e áreas dos outros usuários; ativar/inativar contas.
- Upload de arquivos diretamente para o **Google Drive** usando [google/apiclient](https://github.com/googleapis/google-api-php-client).
- Cada usuário tem **sua pasta** no Drive; criação automática no primeiro upload.
- Limite de **cota por usuário** (em MB); bloqueia upload quando excedido.
- Listagem dos arquivos enviados (IDs e links do Drive).

## Requisitos
- PHP 8.1+ com extensões comuns (pdo_mysql, openssl, mbstring, json, curl).
- MySQL/MariaDB.
- Composer.
- Credenciais do Google Cloud (OAuth Client ID, tipo Web) com Drive API habilitada.

## Setup (5 passos)
1. **Clonar ou copiar** este projeto.
2. **Instalar dependências**:
   ```bash
   composer install
   ```
3. **Criar banco** e tabelas:
   - Importe `database.sql` no seu MySQL.
4. **Configurar ambiente**:
   - Copie `.env.example` para `.env` e ajuste as variáveis (DB, GOOGLE).
   - Baixe o JSON de credenciais OAuth e salve em `storage/google/credentials.json`.
   - Nas *Authorized redirect URIs* do OAuth, adicione: `http(s)://SEU_HOST/auth/google/callback.php`
5. **Executar** (usando servidor embutido do PHP ou Apache/Nginx):
   ```bash
   php -S 0.0.0.0:8080 -t public
   ```
   Abra: `http://localhost:8080/`

## Fluxo OAuth do Google
- Acesse `http://localhost:8080/auth/google/login.php` para conceder acesso.
- O token de acesso/refresh será gravado por usuário em `storage/google/tokens/{user_id}.json`.

## Estrutura
```
php-drive-uploader/
├─ public/
│  ├─ index.php              # Login
│  ├─ register.php           # Registro (opcional, controlado por ADMIN_ENABLE_SELF_REGISTER)
│  ├─ dashboard.php          # Área logada (user)
│  ├─ admin.php              # Painel admin
│  ├─ upload.php             # Upload para Drive
│  ├─ files.php              # Lista arquivos do usuário
│  ├─ logout.php
│  └─ auth/google/
│      ├─ login.php
│      └─ callback.php
├─ src/
│  ├─ bootstrap.php          # sessão, autoload, env, db
│  ├─ Db.php                 # PDO singleton
│  ├─ Auth.php               # login/logout/guard
│  ├─ GoogleDriveClient.php  # client + helpers (folder per user)
│  ├─ Quota.php              # cálculos/cheques de cota
│  └─ Helpers.php            # utilidades
├─ storage/
│  └─ google/
│     ├─ credentials.json    # coloque seu JSON do OAuth aqui
│     └─ tokens/             # tokens por usuário
├─ vendor/                   # composer
├─ composer.json
├─ composer.lock             # (gerado após install)
├─ database.sql
├─ .env.example
├─ config.php                # mapeia env para constantes
├─ README.md
└─ LICENSE
```

## Variáveis de ambiente (.env)
```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=php_drive
DB_USERNAME=root
DB_PASSWORD=

# Registro aberto (true/false)
ADMIN_ENABLE_SELF_REGISTER=true

# Google OAuth
GOOGLE_APP_NAME=PHP Drive Uploader
GOOGLE_REDIRECT_URI=http://localhost:8080/auth/google/callback.php
# Coloque o credentials.json em storage/google/credentials.json
GOOGLE_SCOPES="https://www.googleapis.com/auth/drive.file"
```

## Google Drive: permissões
- **Drive API** habilitada no Console.
- **Scopes**: usamos `drive.file` para permitir o app criar/gerenciar arquivos/pastas que ele mesmo criar.

## Segurança
- Senhas com `password_hash`/`password_verify`.
- CSRF tokens simples nos formulários.
- Prepared Statements (PDO).
- Rate limit simples por IP no login (em memória).

## Comandos úteis
- Criar primeiro admin (se não usar *register*): insira manualmente na tabela `users` (role = 'admin').
- Resetar *OAuth consent* (apagar token do usuário): delete `storage/google/tokens/{id}.json`.

## Licença
MIT. Veja [LICENSE](LICENSE).
