<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\InvalidQuoteRequest;

final readonly class QuoteRequest
{
    /**
     * @param  list<Package>  $packages
     */
    public function __construct(
        private Cep $origin,
        private Cep $destination,
        private array $packages,
        private ?Money $declaredValue = null,
    ) {
        if ($this->packages === []) {
            throw new InvalidQuoteRequest('Pedido de cotação exige ao menos um pacote.');
        }

        if ($this->origin->equals($this->destination)) {
            throw new InvalidQuoteRequest('Origem e destino devem ser distintos.');
        }
    }

    public function origin(): Cep
    {
        return $this->origin;
    }

    public function destination(): Cep
    {
        return $this->destination;
    }

    /**
     * @return list<Package>
     */
    public function packages(): array
    {
        return $this->packages;
    }

    public function declaredValue(): ?Money
    {
        return $this->declaredValue;
    }
}
