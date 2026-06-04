<?php

declare(strict_types=1);

namespace App\Repository\Task;

use App\Utils\Clock;

interface TaskRepository {

    public function create(string $user_uid, string $title): void;

    /** @return Task[] */
    public function findByUserUid(string $user_uid): array;

    /** @return Task[] */
    public function findByUserUidAfterDate(string $user_uid, Clock $date): array;

    public function completeTask(string $task_id): void;

    public function delete(string $task_id): void;

}
