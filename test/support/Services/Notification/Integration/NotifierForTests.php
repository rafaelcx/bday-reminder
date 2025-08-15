<?php

declare(strict_types=1);

namespace Test\Support\Services\Notification\Integration;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Notifier;

class NotifierForTests implements Notifier {

    private \Closure $notify_behavior;
    private \Closure $get_updates_behavior;
    private \Closure $delete_messages_behavior;

    public function __construct() {
        $this->notify_behavior = function() {};
        $this->get_updates_behavior = function() {};
        $this->delete_messages_behavior = function() {};
    }

    public function notify(User $user, Birthday ...$birthday_list): void {
        call_user_func($this->notify_behavior, $user, $birthday_list);
    }

    public function getUpdates(): array {
        return call_user_func($this->get_updates_behavior);
    }

    public function deleteMessages(array $updates): void {
        call_user_func($this->delete_messages_behavior, $updates);
    }

    public function setNotifyBehavior(callable $behavior): void {
        $this->notify_behavior = $behavior;
    }

    public function setGetUpdatesBehavior(callable $behavior): void {
        $this->get_updates_behavior = $behavior;
    }

    public function setDeleteMessagesBehavior(callable $behavior): void {
        $this->delete_messages_behavior = $behavior;
    }

}
