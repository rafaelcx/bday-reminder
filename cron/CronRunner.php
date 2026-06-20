<?php

declare(strict_types=1);

namespace App\Cron;

use App\Logger\LoggerService;
use App\Services\Birthday\BirthdayService;
use App\Services\Task\TaskService;

class CronRunner {

    public static function run(string $task_name): void {
        match ($task_name) {
            'birthday_notify'  => BirthdayService::notify(),
            'update_birthdays' => BirthdayService::add(),
            'task_notify'      => TaskService::notify(),
            'update_tasks'     => TaskService::add(),
            'clean_logs'       => LoggerService::cleanLogs(),

            default => throw new \RuntimeException('Cron task name not configured'),
        };
    }

}
