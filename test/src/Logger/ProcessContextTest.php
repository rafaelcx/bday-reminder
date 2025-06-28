<?php

declare(strict_types=1);

namespace Test\Src\Logger;

use App\Logger\ProcessContext;
use Test\CustomTestCase;

class ProcessContextTest extends CustomTestCase {

    public function testProcessContext(): void {
        ProcessContext::append('key_one', 'value_one');
        ProcessContext::append('key_two', 'value_two');

        $context = ProcessContext::getAll();

        $this->assertCount(2, $context);
        $this->assertSame('value_one', $context['key_one']);
        $this->assertSame('value_two', $context['key_two']);
    }

}
