#!/usr/bin/env php
<?php

declare(strict_types=1);

// To run the script via docker-compose
// docker exec -it bday-reminder-bday-reminder-1 php /app/bin/storage-seed.php

// Copy template files to production /storage directory

$source_dir = 'storage/Files/Templates';
$dest_dir   = 'storage/Files';

foreach (scandir($source_dir) as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    $src_path  = $source_dir . DIRECTORY_SEPARATOR . $file;
    $dest_path = $dest_dir . DIRECTORY_SEPARATOR . $file;

    if (is_file($src_path)) {
        copy($src_path, $dest_path);
    }
}

// Seed files with arbitrary values

foreach (scandir($dest_dir) as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    $file_path = $dest_dir . DIRECTORY_SEPARATOR . $file;

    if (!is_file($file_path)) {
        continue;
    }

    $contents = file_get_contents($file_path);
    $data = json_decode($contents, true);

    if ($data === null) {
        $data = [];
    }

    $data = seedFileData($file, $data);

    $updated_json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($file_path, $updated_json);
}

function seedFileData(string $file, array $data): array {
    switch ($file) {
        case 'birthday-file.json':
            return [
                'birthdays' => [
                    [
                        'uid' => '1',
                        'user_uid' => '1',
                        'name' => 'John Doe',
                        'date' => '1990-01-01 00:00:00',
                        'created_at' => '2025-06-09 11:20:03',
                    ],
                    [
                        'uid' => '2',
                        'user_uid' => '1',
                        'name' => 'Jane Doe',
                        'date' => '1995-06-12 00:00:00',
                        'created_at' => '2025-06-09 11:20:03',
                    ],
                ],
            ];

        case 'credential-file.json':
            return [
                'credentials' => [
                    [
                        'id' => 'telegram-credential',
                        'data' => json_encode(['bot_token' => 'your-token']),
                        'created_at' => '2025-07-06 20:22:00',
                    ],
                ],
            ];

        case 'user-config-file.json':
            return [
                'user_configs' => [
                    [
                        'uid' => '1',
                        'user_uid' => '1',
                        'name' => 'telegram-chat-id',
                        'value' => '0123456789',
                        'created_at' => '2025-07-13',
                        'updated_at' => '2025-07-13',
                    ],
                ],
            ];

        case 'user-file.json':
            return [
                'users' => [
                    [
                        'uid' => '1',
                        'name' => 'Rafael Garcia',
                        'created_at' => '2025-04-25',
                    ],
                ],
            ];

        case 'log-file.json':
            return [];

        default:
            throw new \Exception('There is no seed configured for ' . $file);
    }
}
