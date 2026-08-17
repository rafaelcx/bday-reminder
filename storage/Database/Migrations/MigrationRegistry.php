<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations;

use App\Storage\Database\DatabaseAction;

class MigrationRegistry {

    /**
     * @return DatabaseAction[]
     */
    public function getRegisteredMigrations(): array {
        return [
            new \App\Storage\Database\Migrations\Files\M20260816123000_CreateUsersTableMigration(),
            new \App\Storage\Database\Migrations\Files\M20260816123001_CreateBirthdaysTableMigration(),
            new \App\Storage\Database\Migrations\Files\M20260816123002_CreateCredentialsTableMigration(),
            new \App\Storage\Database\Migrations\Files\M20260816123003_CreateTasksTableMigration(),
            new \App\Storage\Database\Migrations\Files\M20260816123004_CreateUserConfigsTableMigration(),
        ];
    }

}
