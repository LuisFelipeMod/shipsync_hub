<?php

namespace App\Http\Controllers\Api;

use App\Application\Shipping\QuoteRequestFactory;
use App\Application\Shipping\QuoteResultPresenter;
use App\Domain\Shipping\Exceptions\InvalidCep;
use App\Domain\Shipping\Exceptions\InvalidPackage;
use App\Domain\Shipping\Exceptions\InvalidQuoteRequest;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShippingQuoteRequest;
use App\Jobs\ProcessShippingQuoteJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShippingQuoteController extends Controller
{
    public function __construct(
        private readonly QuoteRequestFactory $factory,
        private readonly QuoteResultPresenter $presenter,
    ) {}

    public function store(StoreShippingQuoteRequest $request): JsonResponse
    {
        $payload = $request->validated();

        try {
            $this->factory->fromArray($payload);
        } catch (InvalidCep|InvalidPackage|InvalidQuoteRequest $exception) {
            throw ValidationException::withMessages([
                'request' => [$exception->getMessage()],
            ]);
        }

        $quoteId = (string) Str::uuid();
        ProcessShippingQuoteJob::dispatch($quoteId, $payload);

        return response()->json([
            'id' => $quoteId,
            'status' => 'processing',
        ], 202);
    }

    public function show(string $id, QuoteRepositoryPort $repository): JsonResponse
    {
        $result = $repository->find($id);
        if ($result === null) {
            return response()->json([
                'message' => 'Cotação não encontrada.',
            ], 404);
        }

        return response()->json($this->presenter->toArray($id, $result));
    }
}
