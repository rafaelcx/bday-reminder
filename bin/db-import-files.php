<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Storage\Database\DatabaseActionEngine;
use App\Storage\Database\DatabaseResolver;
use App\Storage\Database\Migrations\MigrationRegistry;

$migration_files = new MigrationRegistry()->getRegisteredMigrations();
$migration_engine = new DatabaseActionEngine(...$migration_files);
$migration_engine->execute();

$root_dir = __DIR__ . '/../storage/Files';
$pdo = DatabaseResolver::resolve();

$mapping = [
    'user-file.json' => [
        'table' => 'users',
        'collection' => 'users',
        'primary_key' => 'uid',
    ],
    'birthday-file.json' => [
        'table' => 'birthdays',
        'collection' => 'birthdays',
        'primary_key' => 'uid',
    ],
    'credential-file.json' => [
        'table' => 'credentials',
        'collection' => 'credentials',
        'primary_key' => 'id',
    ],
    'task-file.json' => [
        'table' => 'tasks',
        'collection' => 'tasks',
        'primary_key' => 'id',
    ],
    'user-config-file.json' => [
        'table' => 'user_configs',
        'collection' => 'user_configs',
        'primary_key' => 'uid',
    ],
];

$files = glob($root_dir . '/*.json');
if ($files === false) {
    exit(0);
}

foreach ($files as $file_path) {
    $file_name = basename($file_path);
    if ($file_name === 'log-file.json' || !isset($mapping[$file_name])) {
        continue;
    }

    $json = file_get_contents($file_path);
    if ($json === false || trim($json) === '') {
        continue;
    }

    $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    $collection_name = $mapping[$file_name]['collection'];
    $records = $payload[$collection_name] ?? [];

    if (!is_array($records) || empty($records)) {
        continue;
    }

    $table = $mapping[$file_name]['table'];
    $primary_key = $mapping[$file_name]['primary_key'];

    foreach ($records as $record) {
        $quoted_columns = [];
        $placeholders = [];

        foreach (array_keys($record) as $column) {
            /** @var string $column */
            $quoted_columns[] = sprintf('"%s"', $column);
            $placeholders[] = ':' . $column;
        }

        $sql = sprintf(
            'INSERT OR REPLACE INTO "%s" (%s) VALUES (%s);',
            $table,
            implode(', ', $quoted_columns),
            implode(', ', $placeholders)
        );

        $stmt = $pdo->prepare($sql);
        $stmt->execute($record);
    }
}
