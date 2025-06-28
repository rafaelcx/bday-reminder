<?php

declare(strict_types=1);

namespace App\Logger;

use App\Storage\FileServiceResolver;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerDefault implements LoggerInterface {

    private string $log_file;

    public function __construct(string $log_file) {
        $this->log_file = $log_file;
    }

    public function emergency(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void {
        $log_as_array = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
        ];

        $log_as_array = array_merge($log_as_array, $context);
        $log_as_string = json_encode($log_as_array) . "\n";
        
        FileServiceResolver::resolve()->putFileContents($this->log_file, $log_as_string, true);
    }

}
