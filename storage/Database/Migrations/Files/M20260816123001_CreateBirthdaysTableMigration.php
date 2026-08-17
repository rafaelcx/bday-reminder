<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations\Files;

use App\Storage\Database\DatabaseResolver;
use App\Storage\Database\Migrations\MigrationInterface;

class M20260816123001_CreateBirthdaysTableMigration implements MigrationInterface {

    public function up(): void {
        $db = DatabaseResolver::resolve();

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS birthdays (
                uid TEXT PRIMARY KEY,
                user_uid TEXT NOT NULL,
                name TEXT NOT NULL,
                date TEXT NOT NULL,
                created_at TEXT NOT NULL
            );
            SQL;

        $db->exec($sql);
    }

    public function down(): void {
        $db = DatabaseResolver::resolve();
        $db->exec('DROP TABLE IF EXISTS birthdays;');
    }
}
