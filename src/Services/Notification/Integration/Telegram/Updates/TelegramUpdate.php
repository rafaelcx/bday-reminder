<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Updates;

use App\Utils\Clock;

class TelegramUpdate {

    public function __construct(
        public readonly string $user_uid,
        public readonly string $birhday_name,
        public readonly Clock $birthday_date,
    ) {}

}
