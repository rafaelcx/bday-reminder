<?php

declare(strict_types=1);

namespace Test\Src\Utils;

use App\Utils\StaticScope;
use Test\CustomTestCase;

class StaticScopeTest extends CustomTestCase {

    public function testStaticScope_GetAndSet(): void {
        $value1 = StaticScope::get('class_1', 'key_1');
        $value2 = StaticScope::get('class_1', 'key_2');
        $value3 = StaticScope::get('class_2', 'key_1');
        $value4 = StaticScope::get('class_2', 'key_2');
        
        $this->assertNull($value1);
        $this->assertNull($value2);
        $this->assertNull($value3);
        $this->assertNull($value4);

        StaticScope::set('class_1', 'key_1', 'value_1');
        StaticScope::set('class_1', 'key_2', 'value_2');
        StaticScope::set('class_2', 'key_1', 'value_3');
        StaticScope::set('class_2', 'key_2', 'value_4');

        $value1 = StaticScope::get('class_1', 'key_1');
        $value2 = StaticScope::get('class_1', 'key_2');
        $value3 = StaticScope::get('class_2', 'key_1');
        $value4 = StaticScope::get('class_2', 'key_2');

        $this->assertSame('value_1', $value1);
        $this->assertSame('value_2', $value2);
        $this->assertSame('value_3', $value3);
        $this->assertSame('value_4', $value4);
    }

    public function testStaticScope_GetOrCreate(): void {
        $value1 = StaticScope::getOrCreate('class_1', 'key_1', fn () => 'factoryValue1');
        $value2 = StaticScope::getOrCreate('class_2', 'key_2', fn () => 'factoryValue2');

        $this->assertSame('factoryValue1', $value1);
        $this->assertSame('factoryValue2', $value2);

        StaticScope::set('class_1', 'key_1', 'value_1');
        StaticScope::set('class_2', 'key_2', 'value_2');

        $value1 = StaticScope::getOrCreate('class_1', 'key_1', fn () => 'factoryValue1');
        $value2 = StaticScope::getOrCreate('class_2', 'key_2', fn () => 'factoryValue2');

        $this->assertSame('value_1', $value1);
        $this->assertSame('value_2', $value2);
    }

    public function testStaticScope_Clear(): void {
        StaticScope::set('class_1', 'key_1', 'value_1');
        StaticScope::set('class_2', 'key_2', 'value_2');

        $value1 = StaticScope::get('class_1', 'key_1');
        $value2 = StaticScope::get('class_2', 'key_2');

        $this->assertSame('value_1', $value1);
        $this->assertSame('value_2', $value2);

        StaticScope::clear();

        $value1 = StaticScope::get('class_1', 'key_1');
        $value2 = StaticScope::get('class_1', 'key_2');

        $this->assertNull($value1);
        $this->assertNull($value2);
    }

}
