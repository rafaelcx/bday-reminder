<?php

declare(strict_types=1);

namespace Test\Storage\Database;

use App\Storage\Database\DatabaseAction;
use App\Storage\Database\DatabaseActionEngine;
use App\Storage\Database\DatabaseResolver;
use Test\DbCustomTestCase;

class DatabaseActionEngineTest extends DbCustomTestCase {

    public function testDatabaseActionEngine(): void {
        $actions = [
            $this->getActionClassForTests('column1'),
            $this->getActionClassForTests('column2'),
            $this->getActionClassForTests('column3'),
        ];

        $database_action_engine = new DatabaseActionEngine(...$actions);
        $db = DatabaseResolver::resolve();

        $database_action_engine->execute();
        $stmt = $db->query("SELECT name FROM test_migrations");
        $this->assertNotFalse($stmt);
        $rows = $stmt->fetchAll();
        $this->assertCount(3, $rows);

        $database_action_engine->rollback();
        $stmt = $db->query("SELECT name FROM test_migrations");
        $this->assertNotFalse($stmt);
        $rows = $stmt->fetchAll();
        $this->assertCount(0, $rows);
    }

    private function getActionClassForTests(string $unique_col): DatabaseAction {
        return new class($unique_col) implements DatabaseAction {

            private string $unique_col;

            public function __construct(string $unique_col) {
                $this->unique_col = $unique_col;
            }

            public function up(): void {
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
