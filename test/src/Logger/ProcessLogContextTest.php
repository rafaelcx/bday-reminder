<?php

declare(strict_types=1);

namespace Test\Src\Logger;

use App\Logger\ProcessLogContext;
use Test\CustomTestCase;

class ProcessLogContextTest extends CustomTestCase {

    public function testProcessLogContext_Set(): void {
        ProcessLogContext::set('key_one', 'value_one');
        ProcessLogContext::set('key_two', 'value_two');

        $context = ProcessLogContext::getAll();

        $this->assertCount(2, $context);
        $this->assertSame('value_one', $context['key_one']);
        $this->assertSame('value_two', $context['key_two']);
    }

    public function testProcessLogContext_Set_NewKeyCreatesBaseKey(): void {
        ProcessLogContext::set('key', 'value_one');
        $context = ProcessLogContext::getAll();

        $this->assertArrayHasKey('key', $context);
        $this->assertSame('value_one', $context['key']);
    }

    public function testProcessLogContext_Set_OnceToExistingKeyAddsDotNumber(): void {
        ProcessLogContext::set('key', 'value_one');
        ProcessLogContext::set('key', 'value_two');
        ProcessLogContext::set('key', 'value_three');

        $context = ProcessLogContext::getAll();

        $this->assertSame('value_one', $context['key']);
        $this->assertSame('value_two', $context['key.1']);
        $this->assertSame('value_three', $context['key.2']);
    }

}
