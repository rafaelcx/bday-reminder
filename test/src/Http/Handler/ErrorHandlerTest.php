<?php

declare(strict_types=1);

namespace Test\Src\Http\Handler;

use App\Storage\FileServiceResolver;
use Test\CustomTestCase;

class ErrorHandlerTest extends CustomTestCase {

    public function testErrorHandler(): void {
        $rs = $this->request_simulator
            ->withMethod('GET')
            ->withPath('/path')
            ->withBody(['key' => 'value']);

        // Simulate routing failure to force an exception within the process
        $rs->withRoutingBehavior(fn() => throw new \Exception('Routing failure'));

        $response = $rs->dispatch();
        
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal Server Error', (string) $response->getBody());

        $log_content = FileServiceResolver::resolve()->getFileContents('log-file.json');
        $this->assertLogStructure($log_content);

        $log_content = json_decode($log_content)[0];
        $this->assertLogsWerePerformed($log_content);
    }

    private function assertLogStructure(string $log_content): void {
        $failure_message = <<< STR
            You have probably changed base incoming HTTP request logs or obligatory error logs.
            If you are sure you want to do that change the assertion method `assertLogsWerePerformed` accordingly.
            If not, than you probably want to add this log inside the execution flow via `ProcessLogContext`. 
            STR;
        $log_content = json_decode($log_content, true)[0];
        $this->assertCount(14, $log_content, $failure_message);
    }

    private function assertLogsWerePerformed(\stdClass $log_content): void {
        // Base incoming HTTP request logs
        $this->assertSame('error', $log_content->level);
        $this->assertSame('http', $log_content->process_type);
        $this->assertSame('/path', $log_content->http_request_path);
        $this->assertSame('GET', $log_content->http_request_method);
        $this->assertSame('[]', $log_content->http_request_query_params);
        $this->assertSame('[]', $log_content->http_request_parsed_params);
        $this->assertNotNull($log_content->process_id);
        $this->assertNotNull($log_content->timestamp);

        // Error handler obligatory logs
        $this->assertSame('500', $log_content->http_response_status);
        $this->assertSame('Process Error', $log_content->message);
        $this->assertSame('Not found.', $log_content->exception_message);
        $this->assertNotNull($log_content->exception_file);
        $this->assertNotNull($log_content->exception_line);
        $this->assertNotNull($log_content->exception_trace);
    }

}
