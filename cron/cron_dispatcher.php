#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Cron\CronRunner;
use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;

$task_name = $argv[1];

ProcessLogContext::set('process_type', 'cron');
ProcessLogContext::set('process_id', uniqid());
ProcessLogContext::set('cron_job', $task_name);

try {
    CronRunner::run($task_name);
} catch (\Throwable $t) {
    ProcessLogContext::set('exception_message', $t->getMessage());
    ProcessLogContext::set('exception_file', $t->getFile());
    ProcessLogContext::set('exception_line', (string) $t->getLine());
    ProcessLogContext::set('exception_trace', $t->getTraceAsString());
} finally {
    $message = 'Process Finished';
    $process_context = ProcessLogContext::getAll();
    LoggerResolver::resolve()->info($message, $process_context);
    echo json_encode($process_context);
}
