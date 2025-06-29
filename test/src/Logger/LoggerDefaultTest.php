<?php

declare(strict_types=1);

namespace Test\Src\Logger;

use App\Logger\LoggerDefault;
use App\Storage\FileServiceResolver;
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

        $this->assertBaseLogData($message, $expected_level, $logged_content);
        $this->assertSame('context_value', $logged_content->context_key);
    }

    public function testLogger_ShouldAppend(): void {
        (new LoggerDefault(self::FILE_NAME))->log(LogLevel::ALERT, 'message1', []);
        (new LoggerDefault(self::FILE_NAME))->log(LogLevel::ALERT, 'message2', []);

        $logged_content = FileServiceResolver::resolve()->getFileContents(self::FILE_NAME);
        
        $exploded_logged_content = explode("\n", trim($logged_content));
        $this->assertCount(2, $exploded_logged_content);
        
        $log_1 = json_decode($exploded_logged_content[0]);
        $log_2 = json_decode($exploded_logged_content[1]);

        $this->assertBaseLogData('message1', LogLevel::ALERT, $log_1);
        $this->assertBaseLogData('message2', LogLevel::ALERT, $log_2);
    }

    private function assertBaseLogData(string $message, string $log_level, \stdClass $logged_content): void {
        $this->assertSame($message, $logged_content->message);
        $this->assertSame($log_level, $logged_content->level);
        $this->assertNotNull($logged_content->timestamp);
    }

}
