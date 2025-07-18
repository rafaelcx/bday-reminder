<?php

declare(strict_types=1);

namespace Test\Support\Services\Notification\Integration;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Notifier;

class NotifierForTests implements Notifier {

    private \Closure $notify_behavior;

    public function __construct() {
        $this->notify_behavior = function() {};
    }

    public function notify(User $user, Birthday ...$birthday_list): void {
        call_user_func($this->notify_behavior, $user, $birthday_list);
    }

    public function setNotifyBehavior(callable $behavior): void {
        $this->notify_behavior = $behavior;
    }

}
