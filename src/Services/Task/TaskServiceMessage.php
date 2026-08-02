<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Repository\Task\Task;
use App\Repository\Task\TaskStatus;
use App\Repository\User\User;

class TaskServiceMessage {

    public static function build(User $user, Task ...$tasks): string {
        $message_lines = [];
        $user_name = $user->name;

        $sorted_tasks = iterator_to_array($tasks);
        uasort($sorted_tasks, self::sortTasks(...));

        if (empty($sorted_tasks)) {
            return <<<TXT
            Here are you tasks:

            ✅ You have no pending tasks!
            TXT;
        }

        $message_lines[] = "Here are your tasks:";
        $message_lines[] = '';

        foreach ($sorted_tasks as $task) {
            $status_icon = $task->status === TaskStatus::DONE ? '✅' : '📋';
            $message_lines[] = "{$status_icon} [{$task->id}] {$task->title}";
        }

        return implode("\n", $message_lines);
    }

    private static function sortTasks(Task $t1, Task $t2): int {
        // Completed tasks go to the botton
        if ($t1->status !== TaskStatus::DOING && $t2->status === TaskStatus::DOING) {
            return 1;
        }
        if ($t1->status === TaskStatus::DOING && $t2->status !== TaskStatus::DOING) {
            return -1;
        }
        // Sort by updated_at descending
        return $t2->updated_at->getTimestamp() <=> $t1->updated_at->getTimestamp();
    }

}
