<?php

declare(strict_types=1);

namespace App\Repository\Task;

use App\Storage\Database\DatabaseResolver;
use App\Utils\Clock;
use PDO;

class TaskRepositoryInSqlite implements TaskRepository {

    public function create(string $user_uid, string $title): void {
        $sql = <<<SQL
            INSERT INTO tasks (id, user_uid, title, status, created_at, updated_at)
            VALUES (:id, :user_uid, :title, :status, :created_at, :updated_at);
            SQL;

        $now = Clock::now()->format('Y-m-d');
        $task_id = $this->nextId($user_uid);

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'id' => $task_id,
            'user_uid' => $user_uid,
            'title' => $title,
            'status' => TaskStatus::DOING->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @return Task[]
     */
    public function findByUserUid(string $user_uid): array {
        $sql = <<<SQL
            SELECT * FROM tasks
            WHERE user_uid = :user_uid
            ORDER BY CASE WHEN status = 'DONE' THEN 0 ELSE 1 END ASC, created_at ASC, id ASC;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute(['user_uid' => $user_uid]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => new Task(
            id: $row['id'],
            user_uid: $row['user_uid'],
            title: $row['title'],
            status: TaskStatus::from($row['status']),
            created_at: Clock::at($row['created_at']),
            updated_at: Clock::at($row['updated_at']),
        ), $rows ?: []);
    }

    /**
     * @return Task[]
     */
    public function findByUserUidAfterDate(string $user_uid, Clock $date): array {
        $sql = <<<SQL
            SELECT * FROM tasks
            WHERE user_uid = :user_uid AND created_at > :filter_date
            ORDER BY CASE WHEN status = 'DONE' THEN 0 ELSE 1 END ASC, created_at ASC, id ASC;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'user_uid' => $user_uid,
            'filter_date' => $date->format('Y-m-d'),
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => new Task(
            id: $row['id'],
            user_uid: $row['user_uid'],
            title: $row['title'],
            status: TaskStatus::from($row['status']),
            created_at: Clock::at($row['created_at']),
            updated_at: Clock::at($row['updated_at']),
        ), $rows ?: []);
    }

    public function completeTask(string $task_id): void {
        $sql = <<<SQL
            UPDATE tasks
            SET status = :status, updated_at = :updated_at
            WHERE id = :id;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute([
            'id' => $task_id,
            'status' => TaskStatus::DONE->value,
            'updated_at' => Clock::now()->format('Y-m-d'),
        ]);
    }

    public function delete(string $task_id): void {
        $sql = <<<SQL
            DELETE FROM tasks WHERE id = :id;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute(['id' => $task_id]);
    }

    private function nextId(string $user_uid): string {
        $sql = <<<SQL
            SELECT MAX(CAST(id AS INTEGER)) FROM tasks;
            SQL;

        $stmt = DatabaseResolver::resolve()->prepare($sql);
        $stmt->execute();
        $max_id = $stmt->fetchColumn();

        return (string) ((int) ($max_id ?: 0) + 1);
    }
}
