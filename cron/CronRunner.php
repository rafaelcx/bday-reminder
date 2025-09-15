<?php

declare(strict_types=1);

namespace App\Cron;

use App\Logger\LoggerService;
use App\Services\Notification\NotificationService;

class CronRunner {

    public static function run(string $task_name): void {
        match ($task_name) {
            'notify'           => NotificationService::notify(),
            'update_birthdays' => NotificationService::add(),
            'clean_logs'       => LoggerService::cleanLogs(),
        };
    }

}
