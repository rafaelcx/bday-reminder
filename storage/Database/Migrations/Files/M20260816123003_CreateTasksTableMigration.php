<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations\Files;

use App\Storage\Database\DatabaseResolver;
use App\Storage\Database\Migrations\MigrationInterface;

class M20260816123003_CreateTasksTableMigration implements MigrationInterface {

    public function up(): void {
        $db = DatabaseResolver::resolve();

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS tasks (
                id TEXT PRIMARY KEY,
                user_uid TEXT NOT NULL,
                title TEXT NOT NULL,
                status TEXT NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            SQL;

        $db->exec($sql);
    }

    public function down(): void {
        $db = DatabaseResolver::resolve();
        $db->exec('DROP TABLE IF EXISTS tasks;');
    }
}
