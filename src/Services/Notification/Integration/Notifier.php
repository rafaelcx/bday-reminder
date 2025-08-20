<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;

interface Notifier {

    public function notify(User $user, Birthday ...$birthday_list): void;

    // TODO: Delete messages should not be exposed as an interface method. It should expose only a getUpdates
    // method since message deletion is specific to this integration, as I want to clear the telegram chat
    // after successfully retrieving updates. Notifier.php has nothing to do with it tough
    
    public function getUpdates(): array;
    public function deleteMessages(array $updates): void;

}
