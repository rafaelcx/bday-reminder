#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Cron\CronRunner;
use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;

$task_name = $argv[1];

ProcessLogContext::append('process_type', 'cron');
ProcessLogContext::append('process_id', uniqid());
ProcessLogContext::append('cron_job', $task_name);

try {
    CronRunner::run($task_name);
} catch (\Throwable $t) {
    ProcessLogContext::append('exception_message', $t->getMessage());
    ProcessLogContext::append('exception_file', $t->getFile());
    ProcessLogContext::append('exception_line', (string) $t->getLine());
    ProcessLogContext::append('exception_trace', $t->getTraceAsString());
} finally {
    $message = 'Process Finished';
    $process_context = ProcessLogContext::getAll();
    LoggerResolver::resolve()->info($message, $process_context);
    echo json_encode($process_context);
}
