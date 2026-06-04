<?php

declare(strict_types=1);

namespace App\Services\Task;

use App\Repository\Task\TaskRepositoryResolver;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Messenger\MessengerResolver;

class TaskService {

    public static function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();

        foreach ($user_list as $user) {
            $task_list = TaskRepositoryResolver::resolve()->findByUserUid($user->uid);

            $message = TaskServiceMessage::build($user, ...$task_list);
            MessengerResolver::resolve()->post($user, $message);
        }
    }

}
