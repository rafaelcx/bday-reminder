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
            Hello {$user_name},

            ✅ You have no pending tasks!

            🎉 Great job! You're all caught up!
            TXT;
        }

        $message_lines[] = "Hello {$user_name},";
        $message_lines[] = '';
        $message_lines[] = "Here are your tasks:";
        $message_lines[] = '';

        foreach ($sorted_tasks as $task) {
            $status_icon = $task->status === TaskStatus::DONE ? '✅' : '📋';
            $message_lines[] = "{$status_icon} [{$task->id}] {$task->title}";
            $message_lines[] = "   Status: {$task->status->value}";
            $message_lines[] = '';
        }

        $message_lines[] = "💪 Keep up the great work!";

        return implode("\n", $message_lines);
    }

    private static function sortTasks(Task $t1, Task $t2): int {
        // Completed tasks go to the start
        if ($t1->status !== TaskStatus::DOING && $t2->status === TaskStatus::DOING) {
            return -1;
        }
        if ($t1->status === TaskStatus::DOING && $t2->status !== TaskStatus::DOING) {
            return 1;
        }
        // Sort by updated_at descending
        return $t2->updated_at->getTimestamp() <=> $t1->updated_at->getTimestamp();
    }

}
