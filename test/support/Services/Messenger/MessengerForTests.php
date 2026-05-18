<?php

declare(strict_types=1);

namespace Test\Support\Services\Messenger;

use App\Repository\User\User;
use App\Services\Messenger\Messenger;
use App\Services\Messenger\Message;

class MessengerForTests implements Messenger {

    private \Closure $post_behavior;
    private \Closure $get_updates_behavior;

    public function __construct() {
        $this->post_behavior = function() {};
        $this->get_updates_behavior = function() { return []; };
    }

    public function post(User $user, string $message): void {
        call_user_func($this->post_behavior, $user, $message);
    }

    /**
     * @return Message[]
     */
    public function getUpdates(User $user): array {
        return call_user_func($this->get_updates_behavior, $user);
    }

    public function setPostBehavior(callable $behavior): void {
        $this->post_behavior = $behavior instanceof \Closure
            ? $behavior
            : \Closure::fromCallable($behavior);
    }

    public function setGetUpdatesBehavior(callable $behavior): void {
        $this->get_updates_behavior = $behavior instanceof \Closure
            ? $behavior
            : \Closure::fromCallable($behavior);
    }

}
