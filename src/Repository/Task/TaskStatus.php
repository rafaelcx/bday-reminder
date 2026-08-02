<?php

declare(strict_types=1);

namespace App\Repository\Task;

enum TaskStatus: string {

    case DONE = 'DONE';
    case DOING = 'DOING';

    public function emoji(): string {
        return match($this) {
            self::DONE => '✅',
            self::DOING => '📋',
        };
    }

}
