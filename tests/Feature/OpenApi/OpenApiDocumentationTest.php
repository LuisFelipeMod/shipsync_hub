<?php

use Symfony\Component\Yaml\Yaml;

describe('Documentação OpenAPI / Swagger UI', function () {
    it('expõe a UI em /api/documentation quando habilitada', function () {
        config(['openapi.ui_enabled' => true]);

        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('swagger-ui', false);
    });

    it('retorna 404 na UI quando desabilitada', function () {
        config(['openapi.ui_enabled' => false]);

        $this->get('/api/documentation')->assertNotFound();
    });

    it('serve a spec YAML versionada v1', function () {
        config(['openapi.ui_enabled' => true]);

        $response = $this->get('/docs/openapi/v1/openapi.yaml');

        $response->assertOk()
            ->assertHeader('content-type', 'application/yaml; charset=utf-8');

        $document = Yaml::parse($response->getContent());
        expect($document['openapi'])->toBe('3.0.3')
            ->and($document['paths'])->toHaveKeys([
                '/api/v1/shipping/quotes',
                '/api/v1/shipping/quotes/{id}',
            ]);
    });

    it('serve a spec em JSON derivada do YAML', function () {
        config(['openapi.ui_enabled' => true]);

        $this->getJson('/docs/openapi/v1/openapi.json')
            ->assertOk()
            ->assertJsonPath('info.title', 'ShipSync Hub API')
            ->assertJsonPath('info.version', '1.0.0');
    });
});

describe('Alinhamento spec v1 × rotas Laravel', function () {
    it('documenta exatamente os paths registrados de cotação', function () {
        $specPath = base_path('docs/openapi/v1/openapi.yaml');
        /** @var array<string, mixed> $document */
        $document = Yaml::parseFile($specPath);

        $documented = array_keys($document['paths']);
        sort($documented);

        $registered = collect(app('router')->getRoutes())
            ->map(fn ($route) => '/'.ltrim($route->uri(), '/'))
            ->filter(fn (string $uri): bool => str_starts_with($uri, '/api/v1/shipping/quotes'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        expect($documented)->toBe($registered);
    });
});
