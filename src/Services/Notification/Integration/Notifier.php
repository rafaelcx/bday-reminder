<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;

interface Notifier {

    public function notify(User $user, Birthday ...$birthday_list): void;

}
