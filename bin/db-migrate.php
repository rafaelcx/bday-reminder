<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Storage\Database\DatabaseActionEngine;
use App\Storage\Database\Migrations\MigrationRegistry;

$migration_files = new MigrationRegistry()->getRegisteredMigrations();
$migration_engine = new DatabaseActionEngine(...$migration_files);
$migration_engine->execute();
