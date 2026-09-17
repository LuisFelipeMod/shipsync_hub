<?php

namespace App\Console\Commands;

use App\Infrastructure\Aws\SqsDeadLetterQueueService;
use Illuminate\Console\Command;

final class ReprocessSqsDeadLetterQueueCommand extends Command
{
    protected $signature = 'shipsync:dlq:reprocess {--limit=10 : Máximo de mensagens a republicar na fila principal}';

    protected $description = 'Republica mensagens da DLQ na fila principal e remove da DLQ';

    public function handle(SqsDeadLetterQueueService $service): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $count = $service->reprocess($limit);

        $this->info("Reprocessadas {$count} mensagem(ns) da DLQ para a fila principal.");

        return self::SUCCESS;
    }
}
