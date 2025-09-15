<?php

declare(strict_types=1);

namespace Test\Src\Logger;

use App\Logger\LoggerDefault;
use App\Storage\FileServiceResolver;
use App\Utils\Clock;
use Psr\Log\LogLevel;
use Test\CustomTestCase;

class LoggerDefaultTest extends CustomTestCase {

    private const FILE_NAME = 'test-log-file.json';

    public static function provideLogExecutionFunction(): iterable {
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->alert($message, $context), LogLevel::ALERT];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->critical($message, $context), LogLevel::CRITICAL];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->debug($message, $context), LogLevel::DEBUG];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->emergency($message, $context), LogLevel::EMERGENCY];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->error($message, $context), LogLevel::ERROR];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->info($message, $context), LogLevel::INFO];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->notice($message, $context), LogLevel::NOTICE];
        yield [fn($message, $context) => (new LoggerDefault(self::FILE_NAME))->warning($message, $context), LogLevel::WARNING];
    }

    /** 
     * @dataProvider provideLogExecutionFunction 
     */
    public function testLogger(callable $log_function, string $expected_level): void {
        $message = 'message';
        $context = ['context_key' => 'context_value'];
        $log_function($message, $context);

        $logged_content = FileServiceResolver::resolve()->getFileContents(self::FILE_NAME);
        $logged_content = json_decode($logged_content);

        $this->assertCount(1, $logged_content);
        $this->assertBaseLogData($message, $expected_level, $logged_content[0]);
        $this->assertSame('context_value', $logged_content[0]->context_key);
    }

    public function testLogger_ShouldAppend(): void {
        new LoggerDefault(self::FILE_NAME)->log(LogLevel::ALERT, 'message1', []);
        new LoggerDefault(self::FILE_NAME)->log(LogLevel::ALERT, 'message2', []);

        $logged_content = FileServiceResolver::resolve()->getFileContents(self::FILE_NAME);
        $logged_content = json_decode($logged_content);
        
        $this->assertCount(2, $logged_content);
        
        $log_1 = $logged_content[0];
        $log_2 = $logged_content[1];

        $this->assertBaseLogData('message1', LogLevel::ALERT, $log_1);
        $this->assertBaseLogData('message2', LogLevel::ALERT, $log_2);
    }

    public function testLogger_CleanLogFile(): void {
        Clock::freeze('2025-09-13 12:00:00');

        $logs = json_encode([
            [
                'timestamp' => '2025-08-01 10:00:00', // 43 days old → should be deleted
                'level' => 'info',
                'message' => 'Old log entry',
            ],
            [
                'timestamp' => '2025-09-10 15:00:00', // 3 days old → should remain
                'level' => 'info',
                'message' => 'Recent log entry',
            ],
        ]);

        FileServiceResolver::resolve()->putFileContents(self::FILE_NAME, $logs);

        new LoggerDefault(self::FILE_NAME)->cleanLogFile();

        $updated_contents = FileServiceResolver::resolve()->getFileContents(self::FILE_NAME);
        $updated_logs = json_decode($updated_contents, true);

        $this->assertCount(1, $updated_logs);
        $this->assertSame('Recent log entry', $updated_logs[0]['message']);
    }

    private function assertBaseLogData(string $message, string $log_level, \stdClass $logged_content): void {
        $this->assertSame($message, $logged_content->message);
        $this->assertSame($log_level, $logged_content->level);
        $this->assertNotNull($logged_content->timestamp);
    }

}
