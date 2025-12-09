<?php

namespace App\Services\Logging;

use App\Services\Logging\Drivers\FileLogger;
use App\Services\Logging\Drivers\DatabaseLogger;

class LogManager implements LogInterface
{
    private array $drivers = [];

    public function __construct()
    {
        // active log drivers
        $this->drivers = [
            new FileLogger(),
            new DatabaseLogger(),
            // create new driver in /Drivers and implements LogInterface
            // then add driver
        ];
    }

    public function info(string $message, array $context = []): void
    {
        foreach ($this->drivers as $driver) {
            $driver->info($message, $context);
        }
    }

    public function warning(string $message, array $context = []): void
    {
        foreach ($this->drivers as $driver) {
            $driver->warning($message, $context);
        }
    }

    public function error(string $message, array $context = []): void
    {
        foreach ($this->drivers as $driver) {
            $driver->error($message, $context);
        }
    }
}
