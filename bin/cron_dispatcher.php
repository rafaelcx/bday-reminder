#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;
use App\Services\Notification\NotificationService;

$task_name = $argv[1];

ProcessLogContext::append('process_type', 'cron');
ProcessLogContext::append('process_id', uniqid());
ProcessLogContext::append('cron_job', $task_name);

match ($task_name) {
    'notify'           => NotificationService::notify(),
    'update_birthdays' => NotificationService::add(),
};

// TODO: Put logging inside a shutdown handler
$message = 'Process Finished';
$process_context = ProcessLogContext::getAll();
LoggerResolver::resolve()->info($message, $process_context);
