# ShipSync Hub

Gateway de logística e fretes — Laravel 11, Clean Architecture, Redis, Memcached, LocalStack (SQS, S3, DynamoDB), Pest (TDD).

## Início rápido

```bash
docker compose up -d
cp .env.example .env   # se ainda não existir
./bin/composer install
./bin/serve            # API + Swagger UI → http://localhost:8000/api/documentation
./bin/test
```

Setup: [docs/setup.md](docs/setup.md) · pastas e camadas: [docs/arquitetura-pastas.md](docs/arquitetura-pastas.md).

**Objetivo, progresso e roadmap:** [docs/desenvolvimento.md](docs/desenvolvimento.md) (leia em novas sessões).

Co-piloto: [`.cursor/skills/`](.cursor/skills/) · conceitos: [`.cursor/docs/`](.cursor/docs/README.md).
