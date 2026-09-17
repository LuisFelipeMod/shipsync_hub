<?php

namespace App\Providers;

use App\Application\Messaging\MessageQueuePort;
use App\Domain\Shipping\QuoteRepositoryPort;
use App\Infrastructure\Aws\AwsClientFactory;
use App\Infrastructure\Aws\DynamoDbQuoteRepository;
use App\Infrastructure\Aws\ShippingQuoteRecordMapper;
use App\Infrastructure\Aws\SqsMessageQueue;
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

        $this->app->bind(MessageQueuePort::class, SqsMessageQueue::class);
        $this->app->bind(QuoteRepositoryPort::class, DynamoDbQuoteRepository::class);
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
