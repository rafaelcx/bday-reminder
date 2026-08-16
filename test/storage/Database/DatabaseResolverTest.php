<?php

declare(strict_types=1);

namespace Test\Storage\Database;

use App\Storage\Database\DatabaseResolver;
use Test\DbCustomTestCase;

class DatabaseResolverTest extends DbCustomTestCase {

    public function testDatabaseResolver(): void {
        $db = DatabaseResolver::resolve();
        $db->exec("
            CREATE TABLE test (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                set_code TEXT NOT NULL
            );
        ");

        // Insert
        $stmt = $db->prepare("
            INSERT INTO test (name, set_code)
            VALUES (:name, :set_code)
        ");

        $stmt->execute([
            'name' => 'Lightning Bolt',
            'set_code' => 'M11'
        ]);

        // Query
        $stmt = $db->query("SELECT * FROM test");
        $this->assertNotFalse($stmt);
        $result = $stmt->fetchAll();

        $this->assertCount(1, $result);
        $this->assertSame('Lightning Bolt', $result[0]['name']);
        $this->assertSame('M11', $result[0]['set_code']);
    }

}
