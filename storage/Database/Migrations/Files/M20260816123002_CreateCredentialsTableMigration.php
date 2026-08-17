<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations\Files;

use App\Storage\Database\DatabaseAction;
use App\Storage\Database\DatabaseResolver;

class M20260816123002_CreateCredentialsTableMigration implements DatabaseAction {

    public function up(): void {
        $db = DatabaseResolver::resolve();

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS credentials (
                id TEXT PRIMARY KEY,
                data TEXT NOT NULL,
                created_at TEXT NOT NULL
            );
            SQL;

        $db->exec($sql);
    }

    public function down(): void {
        $db = DatabaseResolver::resolve();
        $db->exec('DROP TABLE IF EXISTS credentials;');
    }
}
