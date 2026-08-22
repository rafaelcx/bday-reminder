<?php

declare(strict_types=1);

namespace App\Storage\Database\Seed\Files;

use App\Storage\Database\DatabaseAction;
use App\Storage\Database\DatabaseResolver;
use App\Utils\JsonEncoder;

class S20260817000000_SeedStorageData implements DatabaseAction {

    public function up(): void {
        $pdo = DatabaseResolver::resolve();

        $seed_rows = [
            'users' => [
                [
                    'uid' => '1',
                    'name' => 'Rafael Garcia',
                    'created_at' => '2025-04-25',
                ],
            ],
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
            'credentials' => [
                [
                    'id' => 'telegram-credential',
                    'data' => JsonEncoder::safeEncode(['bot_token' => 'your-token']),
                    'created_at' => '2025-07-06 20:22:00',
                ],
            ],
            'tasks' => [
                [
                    'id' => '1',
                    'user_uid' => '1',
                    'title' => 'Buy groceries',
                    'status' => 'DOING',
                    'created_at' => '2026-06-01',
                    'updated_at' => '2026-06-01',
                ],
                [
                    'id' => '2',
                    'user_uid' => '1',
                    'title' => 'Call doctor',
                    'status' => 'DONE',
                    'created_at' => '2026-05-28',
                    'updated_at' => '2026-06-02',
                ],
            ],
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

        foreach ($seed_rows as $table => $rows) {
            $columns = array_keys($rows[0]);
            $quoted_columns = [];
            $placeholders = [];

            foreach ($columns as $column) {
                /** @var string $column */
                $quoted_columns[] = '"' . $column . '"';
                $placeholders[] = ':' . $column;
            }

            $sql = sprintf(
                'INSERT OR REPLACE INTO "%s" (%s) VALUES (%s);',
                $table,
                implode(', ', $quoted_columns),
                implode(', ', $placeholders)
            );

            $stmt = $pdo->prepare($sql);

            foreach ($rows as $row) {
                $stmt->execute($row);
            }
        }
    }

    public function down(): void {
        $pdo = DatabaseResolver::resolve();

        $tables = ['users', 'birthdays', 'credentials', 'tasks', 'user_configs'];
        foreach ($tables as $table) {
            $pdo->exec(sprintf('DELETE FROM "%s";', $table));
        }
    }
}
