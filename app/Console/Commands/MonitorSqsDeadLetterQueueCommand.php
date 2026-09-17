<?php

namespace App\Console\Commands;

use App\Infrastructure\Aws\SqsDeadLetterQueueService;
use Illuminate\Console\Command;

final class MonitorSqsDeadLetterQueueCommand extends Command
{
    protected $signature = 'shipsync:dlq:monitor {--peek=0 : Quantidade de mensagens para exibir (0 = só contagem)}';

    protected $description = 'Exibe métricas aproximadas e opcionalmente faz peek na DLQ SQS';

    public function handle(SqsDeadLetterQueueService $service): int
    {
        $stats = $service->stats();

        $this->info('DLQ SQS (LocalStack ou AWS conforme AWS_ENDPOINT)');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Mensagens visíveis (aprox.)', (string) $stats['approximate_visible']],
                ['Mensagens in-flight (aprox.)', (string) $stats['approximate_not_visible']],
            ],
        );

        $peek = (int) $this->option('peek');
        if ($peek <= 0) {
            return self::SUCCESS;
        }

        $messages = $service->peek($peek);
        if ($messages === []) {
            $this->comment('Nenhuma mensagem disponível para peek.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($messages as $message) {
            $body = $message->body();
            if (strlen($body) > 120) {
                $body = substr($body, 0, 117).'...';
            }
            $rows[] = [$message->messageId(), $body];
        }

        $this->newLine();
        $this->warn('Peek não remove mensagens da DLQ (visibility timeout expira e elas voltam).');
        $this->table(['MessageId', 'Body (truncado)'], $rows);

        return self::SUCCESS;
    }
}
