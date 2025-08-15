<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Notification\Integration\NotifierResolver;

class NotificationService {

    public static function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();
        foreach ($user_list as $user) {
            self::notifyUser($user);
        }
    }

    public static function add(): void {
        $notifier = NotifierResolver::resolve();
        $updates = $notifier->getUpdates();

        // TODO: Create birthday records from updates

        $notifier->deleteMessages($updates);
    }

    private static function notifyUser(User $user): void {
        $user_birthday_list = BirthdayRepositoryResolver::resolve()->findByUserUid($user->uid);
        NotifierResolver::resolve()->notify($user, ...$user_birthday_list);
    }

}
