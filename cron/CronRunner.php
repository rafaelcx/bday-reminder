<?php

declare(strict_types=1);

namespace App\Cron;

use App\Logger\LoggerService;
use App\Services\Interaction\InteractionService;
use App\Services\Notification\NotificationService;

class CronRunner {

    public static function run(string $task_name): void {
        match ($task_name) {
            'process_notifications' => new NotificationService()->process(),
            'process_interactions' => new InteractionService()->process(),
            'clean_logs'       => LoggerService::cleanLogs(),

            default => throw new \RuntimeException('Cron task name not configured'),
        };
    }

}
