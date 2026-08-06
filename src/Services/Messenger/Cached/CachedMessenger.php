<?php

declare(strict_types=1);

namespace App\Services\Messenger\Cached;

use App\Repository\User\User;
use App\Services\Messenger\Messenger;
use App\Services\Messenger\Message;
use App\Utils\StaticScope;

class CachedMessenger implements Messenger {

    public function __construct(
        private readonly Messenger $delegate
    ) {}

    public function post(User $user, string $message): void {
        $this->delegate->post($user, $message);
        StaticScope::set(self::class, 'cache', null);
    }

    /**
     * @return Message[]
     */
    public function getUpdates(): array {
        return StaticScope::getOrCreate(self::class, 'cache', fn() => $this->delegate->getUpdates());
    }

}
