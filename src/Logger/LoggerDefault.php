<?php

declare(strict_types=1);

namespace App\Logger;

use App\Storage\FileServiceResolver;
use App\Utils\Clock;
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
        $log_as_array = $this->buildLogDocument($level, $message, $context);
        $previous_logs = $this->getPreviousLogs();

        $previous_logs[] = $log_as_array;
        $updated_logs = json_encode($previous_logs, JSON_PRETTY_PRINT);
        FileServiceResolver::resolve()->putFileContents($this->log_file, $updated_logs);
    }

    public function cleanLogFile(): void {
        $logs = $this->getPreviousLogs();
        $one_month_ago = Clock::now()->minusDays(30);

        foreach ($logs as $index => $log_entry) {
            if (!isset($log_entry['timestamp'])) {
                continue;
            }

            $log_time = Clock::at($log_entry['timestamp']);

            if ($log_time->isBefore($one_month_ago)) {
                unset($logs[$index]);
            }
        }

        // Reindex to keep JSON as an array, not an object
        $logs = array_values($logs);

        $updated_logs = json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        FileServiceResolver::resolve()->putFileContents($this->log_file, $updated_logs);
    }

    private function buildLogDocument($level, string $message, array $context): array {
        $log_common_fields = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
        ];
        return array_merge($log_common_fields, $context);
    }

    private function getPreviousLogs(): array {
        $file_service = FileServiceResolver::resolve();
        $previous_logs = $file_service->getFileContents($this->log_file);
        
        $previous_logs_decoded = json_decode($previous_logs, true);
        if (!is_array($previous_logs_decoded)) {
            $previous_logs_decoded = [];
        }
        return $previous_logs_decoded;
    }

}
