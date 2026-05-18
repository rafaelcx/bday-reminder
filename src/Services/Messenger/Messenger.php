<?php

declare(strict_types=1);

namespace App\Services\Messenger;

use App\Repository\User\User;

interface Messenger {

    public function post(User $user, string $message): void;

    /**
     * @return Message[]
     */
    public function getUpdates(User $user): array;

}
