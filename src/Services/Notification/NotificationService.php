<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Services\Birthday\BirthdayService;
use App\Services\Task\TaskService;

class NotificationService {

    public function process(): void {
        foreach ($this->getNotifiers() as $notifier) {
            $notifier->notify();
        }
    }

    /**
     * @return Notifier[]
     */
    private function getNotifiers(): array {
        return [
            new BirthdayService(),
            new TaskService(),
        ];
    }

}
