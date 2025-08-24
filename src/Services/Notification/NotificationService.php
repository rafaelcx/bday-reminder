<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Notification\Integration\NotifierResolver;

class NotificationService {

    public static function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();
        
        foreach ($user_list as $user) {
            $user_birthday_list = BirthdayRepositoryResolver::resolve()
                ->findByUserUid($user->uid);

            NotifierResolver::resolve()
                ->notify($user, ...$user_birthday_list);
        }
    }

    public static function add(): void {
        $updates = NotifierResolver::resolve()->getUpdates();

        foreach ($updates as $update) {
            BirthdayRepositoryResolver::resolve()
                ->create($update->user_uid, $update->birhday_name, $update->birthday_date);
        }
    }

}
