<?php

declare(strict_types=1);

namespace Test\Src\Utils;

use App\Utils\Clock;
use Test\CustomTestCase;

class ClockTest extends CustomTestCase {

    /** @before */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 00:00:00');
    }

    public function testClock_Now(): void {
        $date_time = Clock::now();
        $this->assertSame('2025-01-01 00:00:00', $date_time->asDateTimeString());
    }

    public function testClock_At(): void {
        $clock = Clock::at('2025-01-01 12:00:00');
        $this->assertSame('2025-01-01 12:00:00', $clock->asDateTimeString());
    }   

    public static function provideClockFormatsForTests(): iterable {
        yield ['m/d', '01/01'];
        yield ['Y', '2025'];
        yield ['m', '01'];
        yield ['d', '01'];
    }
    
    /** @dataProvider provideClockFormatsForTests */
    public function testClock_Format(string $format, string $expected_str): void {
        $this->assertSame($expected_str, Clock::now()->format($format));
    }

    public function testClock_GetTimestamp(): void {
        $this->assertSame(1735689600, Clock::now()->getTimestamp());
    }

    public function testClock_AsDateTimeString(): void {
        $this->assertSame('2025-01-01 00:00:00', Clock::now()->asDateTimeString());
    }

    public function testClock_AsDateString(): void {
        $this->assertSame('2025-01-01', Clock::now()->asDateString());
    }

    public function testClock_PlusDays(): void {
        $this->assertSame('2025-01-11', Clock::now()->plusDays(10)->asDateString());
    }

    public function testClock_MinusDays(): void {
        $this->assertSame('2024-12-22', Clock::now()->minusDays(10)->asDateString());
    }

    public function testClock_PlusYears(): void {
        $this->assertSame('2026-01-01 00:00:00', Clock::now()->plusYears(1)->asDateTimeString());
    }

    public function testClock_MinusYears(): void {
        $this->assertSame('2024-01-01 00:00:00', Clock::now()->minusYears(1)->asDateTimeString());
    }

    public function testClock_IsAfterAndIsBefore(): void {
        $c1 = Clock::at('2025-01-01 12:00:00');
        $c2 = Clock::at('2025-01-01 12:00:01');
        
        $this->assertTrue($c2->isAfter($c1));
        $this->assertFalse($c2->isBefore($c1));

        $this->assertTrue($c1->isBefore($c2));
        $this->assertFalse($c1->isAfter($c2));
    }

}
