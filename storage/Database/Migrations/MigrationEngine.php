<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations;

class MigrationEngine {

    /** @var MigrationInterface[] $migrations */
    private array $migrations;

    public function __construct(MigrationInterface ...$migrations) {
        $this->migrations = $migrations;
    }

    public function migrate(): void {
        foreach ($this->migrations as $migration) {
            $migration->up();
        }
    }

    public function rollback(): void {
        foreach (array_reverse($this->migrations) as $migration) {
            $migration->down();
        }
    }

}
