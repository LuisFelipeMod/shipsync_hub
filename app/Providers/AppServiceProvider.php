<?php

namespace App\Providers;

use App\Application\Messaging\MessageQueuePort;
use App\Application\Observability\TransactionTracerPort;
use App\Application\Shipping\QuoteCachePort;
use App\Domain\Shipping\CarrierQuotePort;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Infrastructure\Aws\AwsClientFactory;
use App\Infrastructure\Aws\DynamoDbQuoteRepository;
use App\Infrastructure\Aws\ShippingQuoteRecordMapper;
use App\Infrastructure\Aws\SqsDeadLetterQueueService;
use App\Infrastructure\Aws\SqsMessageQueue;
use App\Infrastructure\Observability\NewRelicTransactionTracer;
use App\Infrastructure\Observability\NoOpTransactionTracer;
use App\Infrastructure\Cache\LaravelQuoteCache;
use App\Infrastructure\Carriers\CarrierQuotePortFactory;
use App\Infrastructure\Testing\InMemoryQuoteRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AwsClientFactory::class, fn () => AwsClientFactory::fromLaravelConfig(
            config('services.aws'),
        ));

        $this->app->bind(MessageQueuePort::class, function ($app): SqsMessageQueue {
            return SqsMessageQueue::main($app->make(AwsClientFactory::class));
        });
        $this->app->singleton(SqsDeadLetterQueueService::class, function ($app): SqsDeadLetterQueueService {
            $factory = $app->make(AwsClientFactory::class);

            return new SqsDeadLetterQueueService(
                SqsMessageQueue::deadLetter($factory),
                SqsMessageQueue::main($factory),
            );
        });
        $this->app->singleton(TransactionTracerPort::class, function (): TransactionTracerPort {
            $newRelic = new NewRelicTransactionTracer;
            if ($newRelic->isEnabled()) {
                return $newRelic;
            }

            return new NoOpTransactionTracer;
        });
        if ($this->app->environment('testing')) {
            $this->app->singleton(InMemoryQuoteRepository::class);
            $this->app->bind(QuoteRepositoryPort::class, InMemoryQuoteRepository::class);
        } else {
            $this->app->bind(QuoteRepositoryPort::class, DynamoDbQuoteRepository::class);
        }
        $this->app->singleton(CarrierQuotePortFactory::class);
        $this->app->bind(CarrierQuotePort::class, fn ($app) => $app->make(CarrierQuotePortFactory::class)->create());
        $this->app->bind(QuoteCachePort::class, function ($app): LaravelQuoteCache {
            return new LaravelQuoteCache(
                $app->make('cache')->store(),
                (int) config('shipping.quote_cache_ttl_seconds', 3600),
            );
        });
        $this->app->singleton(ShippingQuoteRecordMapper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
