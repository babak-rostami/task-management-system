<?php

namespace App\Services\Logging\Drivers;

use App\Models\ActionLog;
use App\Services\Logging\LogInterface;

class DatabaseLogger implements LogInterface
{
    public function info(string $message, array $context = []): void
    {
        $this->store('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->store('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->store('error', $message, $context);
    }

    private function store(string $level, string $message, array $context)
    {
        ActionLog::create([
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
        ]);
    }
}
