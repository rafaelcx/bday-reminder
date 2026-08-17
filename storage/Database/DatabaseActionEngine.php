<?php

declare(strict_types=1);

namespace App\Storage\Database;

class DatabaseActionEngine {

    /** @var DatabaseAction[] $actions */
    private array $actions;

    public function __construct(DatabaseAction ...$actions) {
        $this->actions = $actions;
    }

    public function execute(): void {
        foreach ($this->actions as $action) {
            $action->up();
        }
    }

    public function rollback(): void {
        foreach (array_reverse($this->actions) as $action) {
            $action->down();
        }
    }

}
