# OpenAPI / Swagger no ShipSync Hub

## Por que spec versionada no repo

- Contrato HTTP explícito para integradores e para revisão em PR.
- Cada versão de URL (`/api/v1`) tem pasta `docs/openapi/v1/` — breaking change → `v2`, não misturar paths.
- Teste Feature garante que paths da spec batem com rotas Laravel.

## Por que Swagger UI separada da spec

- UI (`/api/documentation`) é conveniência de dev; produção desliga com `OPENAPI_UI_ENABLED=false`.
- A fonte de verdade é o YAML commitado, não anotações espalhadas só na UI.

## Erros comuns

| Sintoma | Causa provável |
|---------|----------------|
| UI 404 | `OPENAPI_UI_ENABLED=false` ou `APP_ENV=production` sem override |
| Spec desatualizada vs API | Alterou controller/rotas sem editar `docs/openapi/v1/openapi.yaml` |
| Teste de alinhamento falha | Path novo em `routes/api.php` não refletido na spec |
