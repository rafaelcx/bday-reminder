<?php

declare(strict_types=1);

namespace App\Utils;

class StopWatch {

    private ?float $start_time = null;
    private float $elapsed = 0.0;
    private bool $is_running = false;

    public function start(): void {
        if (!$this->is_running) {
            $this->start_time = microtime(true);
            $this->is_running = true;
        }
    }

    public function stop(): void {
        if ($this->is_running && $this->start_time !== null) {
            $this->elapsed += microtime(true) - $this->start_time;
            $this->start_time = null;
            $this->is_running = false;
        }
    }

    public function getTime(): int {
        $current_elapsed = $this->elapsed;

        if ($this->is_running && $this->start_time !== null) {
            $current_elapsed += microtime(true) - $this->start_time;
        }

        return (int) round($current_elapsed * 1000); // milliseconds
    }

}
