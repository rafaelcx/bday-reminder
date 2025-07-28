#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;
use App\Services\Notification\NotificationService;

ProcessLogContext::append('process_type', 'cron');
ProcessLogContext::append('process_id', uniqid());
ProcessLogContext::append('cron_job', 'notify_users');

NotificationService::notify();

$message = 'Process Finished';
$process_context = ProcessLogContext::getAll();
LoggerResolver::resolve()->info($message, $process_context);
