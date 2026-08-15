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

        $intro_text = <<<TXT
        Here are you tasks {$user_name}.

        The first ones are pending while the things you done in the last two weeks shows on the botton.
        TXT;

        $doing_tasks = array_filter($sorted_tasks, fn($task) => $task->status === TaskStatus::DOING);
        $done_tasks = array_filter($sorted_tasks, fn($task) => $task->status === TaskStatus::DONE);;

        if (empty($doing_tasks)) {
            $no_tasks_text = "\n\n✅ You have no pending tasks though, congrats!";
            return $intro_text . $no_tasks_text;
        }

        $message_lines[] = $intro_text;
        $message_lines[] = '';

        foreach ($doing_tasks as $task) {
            $message_lines[] = "📋 [{$task->id}] {$task->title}";
        }

        $message_lines[] = '';

        foreach ($done_tasks as $task) {
            $message_lines[] = "✅ [{$task->id}] {$task->title}";
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
