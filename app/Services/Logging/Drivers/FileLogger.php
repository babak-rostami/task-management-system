<?php

namespace App\Services\Logging\Drivers;

use App\Services\Logging\LogInterface;
use Illuminate\Support\Facades\Log;

class FileLogger implements LogInterface
{
    public function info(string $message, array $context = []): void
    {
        Log::channel('daily')->info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        Log::channel('daily')->warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::channel('daily')->error($message, $context);
    }
}
