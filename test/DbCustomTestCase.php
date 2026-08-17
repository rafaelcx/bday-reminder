<?php

declare(strict_types=1);

namespace Test;

use App\Storage\Database\DatabaseActionEngine;
use App\Storage\Database\Migrations\MigrationRegistry;
use PHPUnit\Framework\Attributes\Before;
use Test\Support\DatabaseResolverForTests;

class DbCustomTestCase extends CustomTestCase {

    #[Before()]
    public function setUpDatabaseForTests(): void {
        DatabaseResolverForTests::override();
        $this->runMigrations();
    }

    private function runMigrations(): void {
        $migration_files = new MigrationRegistry()->getRegisteredMigrations();
        $migration_engine = new DatabaseActionEngine(...$migration_files);
        $migration_engine->execute();
    }

}
