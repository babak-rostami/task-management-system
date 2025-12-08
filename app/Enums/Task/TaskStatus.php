<?php

namespace App\Enums\Task;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
