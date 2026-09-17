<?php

use App\Domain\Shipping\QuoteRepositoryPort;
use App\Domain\Shipping\QuoteResult;
use App\Jobs\ProcessShippingQuoteJob;
use Illuminate\Support\Facades\Queue;

describe('API de cotação de frete', function () {
    it('aceita pedido válido e enfileira processamento', function () {
        Queue::fake();

        $response = $this->postJson('/api/v1/shipping/quotes', [
            'origin' => '01310-100',
            'destination' => '20040-020',
            'packages' => [
                [
                    'weight_grams' => 1000,
                    'length_cm' => 20,
                    'width_cm' => 15,
                    'height_cm' => 10,
                ],
            ],
        ]);

        $response->assertAccepted()
            ->assertJsonStructure(['id', 'status'])
            ->assertJson(['status' => 'processing']);

        Queue::assertPushed(ProcessShippingQuoteJob::class, function (ProcessShippingQuoteJob $job) use ($response): bool {
            return $job->quoteId === $response->json('id');
        });
    });

    it('rejeita CEP inválido', function () {
        $response = $this->postJson('/api/v1/shipping/quotes', [
            'origin' => 'invalid',
            'destination' => '20040-020',
            'packages' => [
                [
                    'weight_grams' => 1000,
                    'length_cm' => 20,
                    'width_cm' => 15,
                    'height_cm' => 10,
                ],
            ],
        ]);

        $response->assertUnprocessable();
    });

    it('retorna resultado após job processar com fila sync', function () {
        $create = $this->postJson('/api/v1/shipping/quotes', [
            'origin' => '01310-100',
            'destination' => '20040-020',
            'packages' => [
                [
                    'weight_grams' => 1000,
                    'length_cm' => 20,
                    'width_cm' => 15,
                    'height_cm' => 10,
                ],
            ],
        ]);

        $create->assertAccepted();
        $id = $create->json('id');

        $show = $this->getJson("/api/v1/shipping/quotes/{$id}");

        $show->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('cheapest.carrier_id', 'correios')
            ->assertJsonStructure([
                'id',
                'status',
                'quotes',
                'cheapest',
                'fastest',
            ]);
    });

    it('retorna 404 quando cotação ainda não existe', function () {
        $this->getJson('/api/v1/shipping/quotes/00000000-0000-4000-8000-000000000000')
            ->assertNotFound();
    });

    it('expõe resultado persistido pelo repositório', function () {
        $repository = $this->app->make(QuoteRepositoryPort::class);
        $request = app(\App\Application\Shipping\QuoteRequestFactory::class)->fromArray([
            'origin' => '01310-100',
            'destination' => '20040-020',
            'packages' => [
                [
                    'weight_grams' => 500,
                    'length_cm' => 10,
                    'width_cm' => 10,
                    'height_cm' => 10,
                ],
            ],
        ]);
        $result = new QuoteResult([
            new \App\Domain\Shipping\Quote('manual', 'test', new \App\Domain\Shipping\Money(999), 1),
        ]);
        $repository->save('fixed-id', $request, $result);

        $this->getJson('/api/v1/shipping/quotes/fixed-id')
            ->assertOk()
            ->assertJsonPath('cheapest.price_cents', 999);
    });
});
