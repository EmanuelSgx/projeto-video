<?php

namespace App\Contracts;

interface QueueServiceInterface
{
    /**
     * Dispatch job to queue
     */
    public function dispatch(string $jobClass, array $data): void;

    /**
     * Send message to queue
     */
    public function sendMessage(string $queue, array $message): void;
}
