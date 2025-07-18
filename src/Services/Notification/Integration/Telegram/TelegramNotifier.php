<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Notifier;

class TelegramNotifier implements Notifier {

    public function notify(User $user, Birthday ...$birthday_list): void {
        TelegramNotifyRequestBuilder::build($user, ...$birthday_list);
    }

}
