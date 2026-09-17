<?php

namespace App\Console\Commands;

use App\Application\Shipping\QuoteRequestFactory;
use App\Application\Shipping\QuoteResultPresenter;
use App\Application\Shipping\RequestShippingQuoteUseCase;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RequestShippingQuoteCommand extends Command
{
    protected $signature = 'shipping:quote
        {origin : CEP de origem}
        {destination : CEP de destino}
        {--weight=1000 : Peso em gramas}
        {--length=20 : Comprimento em cm}
        {--width=15 : Largura em cm}
        {--height=10 : Altura em cm}';

    protected $description = 'Solicita cotação de frete (caso de uso síncrono, sem fila)';

    public function handle(
        QuoteRequestFactory $factory,
        RequestShippingQuoteUseCase $useCase,
        QuoteResultPresenter $presenter,
    ): int {
        $payload = [
            'origin' => (string) $this->argument('origin'),
            'destination' => (string) $this->argument('destination'),
            'packages' => [[
                'weight_grams' => (int) $this->option('weight'),
                'length_cm' => (int) $this->option('length'),
                'width_cm' => (int) $this->option('width'),
                'height_cm' => (int) $this->option('height'),
            ]],
        ];

        $id = (string) Str::uuid();
        $result = $useCase->execute($id, $factory->fromArray($payload));

        $this->line(json_encode($presenter->toArray($id, $result), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
