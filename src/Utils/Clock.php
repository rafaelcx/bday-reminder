<?php

declare(strict_types=1);

namespace App\Utils;

use DateInterval;

class Clock {

    private const DEFAULT_TIME_ZONE = 'UTC';

    private static ?\DateTimeImmutable $freeze = null;
    
    private \DateTimeImmutable $date_time;

    private function __construct(\DateTimeImmutable $date_time) {
        $this->date_time = $date_time;
    }

    public static function now(): self {
        $date_time_zone = new \DateTimeZone(self::DEFAULT_TIME_ZONE);
        $date_time = new \DateTimeImmutable('now', $date_time_zone);

        return is_null(self::$freeze)
            ? new self($date_time)
            : new self(self::$freeze);
    }

    public static function at(string $date_time): self {
        $date_time_zone = new \DateTimeZone(self::DEFAULT_TIME_ZONE);
        $date_time = new \DateTimeImmutable($date_time, $date_time_zone);
        return new self($date_time);
    }

    public function format(string $format): string {
        return $this->date_time->format($format);
    }

    public function getTimestamp(): int {
        return $this->date_time->getTimestamp();
    }

    public function asDateString(): string {
        return $this->date_time->format('Y-m-d');
    }

    public function asDateTimeString(): string {
        return $this->date_time->format('Y-m-d H:i:s');
    }

    public function plusYears(int $years): self {
        $updated_dt = $this->date_time->modify("+{$years} year");
        $this->date_time = $updated_dt;
        return $this;
    }

    public function minusYears(int $years): self {
        $updated_dt = $this->date_time->modify("-{$years} year");
        $this->date_time = $updated_dt;
        return $this;
    }

    public function plusDays(int $days): self {
        $updated_dt = $this->date_time->add(new \DateInterval("P{$days}D"));
        $this->date_time = $updated_dt;
        return $this;
    }

    public function minusDays(int $days): self {
        $updated_dt = $this->date_time->sub(new \DateInterval("P{$days}D"));
        $this->date_time = $updated_dt;
        return $this;
    }

    public function isAfter(Clock $other): bool {
        return $this->date_time->getTimestamp() > $other->date_time->getTimestamp();
    }

    public function isBefore(Clock $other): bool {
        return $this->date_time->getTimestamp() < $other->date_time->getTimestamp();
    }

    public function diff(Clock $other): DateInterval {
        return $this->date_time->diff($other->date_time);
    }

    public static function freeze(string $date_time): void {
        $date_time_zone = new \DateTimeZone(self::DEFAULT_TIME_ZONE);
        self::$freeze = new \DateTimeImmutable($date_time, $date_time_zone);
    }

    public static function reset(): void {
        self::$freeze = null;
    }

}
