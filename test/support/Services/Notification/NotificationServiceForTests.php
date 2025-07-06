<?php

declare(strict_types=1);

namespace Test\Support\Services\Notification;

use App\Services\Notification\NotificationService;

class NotificationServiceForTests implements NotificationService {

    private \Closure $notify_behavior;

    public function __construct() {
        $this->notify_behavior = function() {};
    }
    
    public function notify(): void {
        call_user_func($this->notify_behavior);
    }

    public function setNotifyBehavior(callable $behavior): void {
        $this->notify_behavior = $behavior;
    }

}
