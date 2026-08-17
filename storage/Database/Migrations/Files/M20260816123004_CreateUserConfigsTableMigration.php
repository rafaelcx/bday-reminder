<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations\Files;

use App\Storage\Database\DatabaseAction;
use App\Storage\Database\DatabaseResolver;

class M20260816123004_CreateUserConfigsTableMigration implements DatabaseAction {

    public function up(): void {
        $db = DatabaseResolver::resolve();

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS user_configs (
                uid TEXT PRIMARY KEY,
                user_uid TEXT NOT NULL,
                name TEXT NOT NULL,
                value TEXT NOT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            SQL;

        $db->exec($sql);
    }

    public function down(): void {
        $db = DatabaseResolver::resolve();
        $db->exec('DROP TABLE IF EXISTS user_configs;');
    }
}
