<?php

declare(strict_types=1);

namespace Test\Src\Utils;

use App\Utils\StopWatch;
use Test\CustomTestCase;

class StopWatchTest extends CustomTestCase {

    public function testStopWatch_BasicStartStop(): void {
        $stopwatch = new StopWatch();
        $stopwatch->start();

        usleep(100_000);
        $stopwatch->stop();

        $time = $stopwatch->getTime();
        $this->assertGreaterThanOrEqual(100, $time);
        $this->assertLessThan(200, $time);
    }

    public function testStopWatch_MultipleStartStopCycles(): void {
        $stopwatch = new StopWatch();
        $stopwatch->start();

        usleep(100_000);
        $stopwatch->stop();

        $firstTime = $stopwatch->getTime();
        $this->assertGreaterThanOrEqual(100, $firstTime);

        $stopwatch->start();
        usleep(200_000);
        $stopwatch->stop();

        $totalTime = $stopwatch->getTime();
        $this->assertGreaterThanOrEqual(300, $totalTime);
        $this->assertLessThan(400, $totalTime);
    }

    public function testStopWatch_GetTimeWhileRunning(): void {
        $stopwatch = new StopWatch();
        $stopwatch->start();
        usleep(100_000);

        $mid_time = $stopwatch->getTime();
        $this->assertGreaterThanOrEqual(100, $mid_time);

        $stopwatch->stop();
    }

    public function testStopWatch_GetTimeWithoutStart(): void {
        $stopwatch = new StopWatch();
        $this->assertSame(0, $stopwatch->getTime());
    }

}
