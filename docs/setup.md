# Setup — ShipSync Hub

Objetivo, progresso e próximas etapas: [`desenvolvimento.md`](desenvolvimento.md).

Conceitos técnicos (co-piloto): [`.cursor/docs/README.md`](../.cursor/docs/README.md).

## Pré-requisitos

- Docker + Compose (`docker compose`)
- PHP **8.4+** e Composer no host (para `./vendor/bin/pest` direto), ou use o container (abaixo)

## Infra local

```bash
docker compose up -d
curl -s http://localhost:4566/_localstack/health | head -c 200
```

## Dependências PHP

Com PHP no host:

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Sem PHP no host (usa container `app`):

```bash
cp .env.example .env
chmod +x bin/composer bin/test
./bin/composer install
docker compose --profile dev run --rm app php artisan key:generate
docker compose --profile dev run --rm app touch database/database.sqlite
docker compose --profile dev run --rm app php artisan migrate
```

### Fedora — extensões PHP (CLI)

O `php-cli` sozinho **não** traz `ext-dom` / `ext-xml`. Laravel, PHPUnit e Pest precisam delas.

```bash
sudo dnf install php-cli php-xml php-mbstring php-zip php-sqlite3 composer
php -m | grep -E '^(dom|xml|mbstring|zip|pdo_sqlite)$'
```

Se `composer install` avisar que o lock está desatualizado em relação ao `composer.json`:

```bash
composer update --lock
composer install
```

Evite `--ignore-platform-req=ext-dom` em dev: você ficaria sem extensões reais ao rodar Pest/Artisan.

Sem PHP local, Pest via imagem oficial:

```bash
docker compose up -d
docker run --rm -v "$PWD:/app" -w /app --network host php:8.4-cli ./vendor/bin/pest
```

O `composer.json` define `"audit": { "block-insecure": false }` para permitir lock em dev; revise advisories antes de produção.

## Testes

Infra no ar + dependências instaladas:

```bash
# Host (127.0.0.1 nas portas publicadas)
./vendor/bin/pest
./vendor/bin/pest --filter=LocalServices

# Ou via container (hosts = nomes dos serviços Docker)
./bin/test --in-container --filter=LocalServices
```
