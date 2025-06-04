<?php

namespace App\Services;

use App\Contracts\QueueServiceInterface;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class LaravelQueueService implements QueueServiceInterface
{
    public function dispatch(string $jobClass, array $data): void
    {
        if (!class_exists($jobClass)) {
            throw new \InvalidArgumentException("Job class {$jobClass} does not exist");
        }

        Queue::push($jobClass, $data);
    }

    public function sendMessage(string $queue, array $message): void
    {
        try {
            // Para desenvolvimento, vamos usar o log como fila simples
            // Em produção, isso pode ser substituído por RabbitMQ, SQS, etc.
            Log::channel('single')->info("Queue Message [{$queue}]", [
                'queue' => $queue,
                'message' => $message,
                'timestamp' => now()->toISOString()
            ]);

            // Alternatively, use Laravel's queue system
            Queue::push('ProcessVideoMessage', [
                'queue' => $queue,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send message to queue [{$queue}]: " . $e->getMessage(), [
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
