<?php

declare(strict_types=1);

namespace App\Services\Messenger;

class Message {

    public function __construct(
        public readonly string $id,
        public readonly string $message_id,
        public readonly string $user_uid,
        public readonly string $text,
    ) {}

}
