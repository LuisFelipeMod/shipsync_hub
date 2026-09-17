<?php

namespace App\Domain\Shipping;

use App\Domain\Shipping\Exceptions\EmptyQuoteResult;

final readonly class QuoteResult
{
    /**
     * @param  list<Quote>  $quotes
     */
    public function __construct(private array $quotes)
    {
        if ($this->quotes === []) {
            throw new EmptyQuoteResult('Resultado de cotação não pode ser vazio.');
        }
    }

    /**
     * @return list<Quote>
     */
    public function all(): array
    {
        return $this->quotes;
    }

    public function cheapest(): Quote
    {
        return array_reduce(
            array_slice($this->quotes, 1),
            fn (Quote $cheapest, Quote $quote): Quote => $quote->price()->lessThan($cheapest->price())
                ? $quote
                : $cheapest,
            $this->quotes[0],
        );
    }

    public function fastest(): Quote
    {
        return array_reduce(
            array_slice($this->quotes, 1),
            fn (Quote $fastest, Quote $quote): Quote => $quote->deliveryDays() < $fastest->deliveryDays()
                ? $quote
                : $fastest,
            $this->quotes[0],
        );
    }
}
