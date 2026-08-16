<?php

declare(strict_types=1);

namespace Test\Storage\Database\Migrations;

use App\Storage\Database\DatabaseResolver;
use App\Storage\Database\Migrations\MigrationEngine;
use App\Storage\Database\Migrations\MigrationInterface;
use Test\DbCustomTestCase;

class MigrationEngineTest extends DbCustomTestCase {

    public function testMigrationEngine(): void {
        $migrations = [
            $this->getMigrationClassForTests('column1'),
            $this->getMigrationClassForTests('column2'),
            $this->getMigrationClassForTests('column3'),
        ];

        $migration_engine = new MigrationEngine(...$migrations);
        $db = DatabaseResolver::resolve();

        $migration_engine->migrate();
        $stmt = $db->query("SELECT name FROM test_migrations");
        $this->assertNotFalse($stmt);
        $rows = $stmt->fetchAll();
        $this->assertCount(3, $rows);

        $migration_engine->rollback();
        $stmt = $db->query("SELECT name FROM test_migrations");
        $this->assertNotFalse($stmt);
        $rows = $stmt->fetchAll();
        $this->assertCount(0, $rows);
    }

    private function getMigrationClassForTests(string $unique_col): MigrationInterface {
        return new class($unique_col) implements MigrationInterface {

            private string $unique_col;

            public function __construct(string $unique_col) {
                $this->unique_col = $unique_col;
            }

            function up(): void {
                $db = DatabaseResolver::resolve();
                $db->exec("CREATE TABLE IF NOT EXISTS test_migrations (name TEXT);");
                $db->exec("INSERT INTO test_migrations (name) VALUES ('{$this->unique_col}');");
            }

            public function down(): void {
                $db = DatabaseResolver::resolve();
                $db->exec("DELETE FROM test_migrations WHERE name LIKE '{$this->unique_col}';");
            }
        };
    }

}
