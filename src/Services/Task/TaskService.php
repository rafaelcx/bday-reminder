<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Repository\Task\TaskRepositoryResolver;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Interaction\Interactor;
use App\Services\Messenger\MessengerResolver;
use App\Services\Notification\Notifier;

class TaskService implements Notifier, Interactor {

    public function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();

        foreach ($user_list as $user) {
            $task_list = TaskRepositoryResolver::resolve()->findByUserUid($user->uid);

            $message = TaskServiceMessage::build($user, ...$task_list);
            MessengerResolver::resolve()->post($user, $message);
        }
    }

    public function processInteractions(): void {
        $updates = MessengerResolver::resolve()->getUpdates();

        foreach ($updates as $update) {
            $parts = str_getcsv($update->text, ' ', '"', '\\');
            
            $service = $parts[0] ?? '';
            $command = $parts[1] ?? '';

            if ($service !== 'task' || $command !== 'add') {
                continue;
            }

            $title = $parts[2] ?? '';

            if (empty($title)) {
                throw new \Exception('Task service `add` got unexpected params');
            }

            TaskRepositoryResolver::resolve()
                ->create($update->user_uid, $title);
        }
    }

}
