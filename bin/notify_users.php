#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Logger\LoggerResolver;
use App\Logger\ProcessLogContext;
use App\Services\Notification\NotificationService;

NotificationService::notify();

$message = 'app_cli';
$process_context = ProcessLogContext::getAll();
LoggerResolver::resolve()->info($message, $process_context);
