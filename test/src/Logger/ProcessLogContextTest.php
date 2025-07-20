<?php

declare(strict_types=1);

namespace Test\Src\Logger;

use App\Logger\ProcessLogContext;
use Test\CustomTestCase;

class ProcessLogContextTest extends CustomTestCase {

    public function testProcessLogContext(): void {
        ProcessLogContext::append('key_one', 'value_one');
        ProcessLogContext::append('key_two', 'value_two');

        $context = ProcessLogContext::getAll();

        $this->assertCount(2, $context);
        $this->assertSame('value_one', $context['key_one']);
        $this->assertSame('value_two', $context['key_two']);
    }

}
