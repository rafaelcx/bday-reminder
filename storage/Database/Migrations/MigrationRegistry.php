<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations;

class MigrationRegistry {

    /**
     * @return MigrationInterface[]
     */
    public function getRegisteredMigrations(): array {
        return [
            new \App\Storage\Database\Migrations\Files\M20260816123000_CreateUsersTableMigration(),
        ];
    }

}
