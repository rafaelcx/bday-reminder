<?php

declare(strict_types=1);

namespace App\Services\Birthday;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Messenger\MessengerResolver;
use App\Utils\Clock;

class BirthdayService {

    public static function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();
        
        foreach ($user_list as $user) {
            $user_birthday_list = BirthdayRepositoryResolver::resolve()
                ->findByUserUidInTheNextDays($user->uid, 30);

            $message = BirthdayServiceMessage::build($user, ...$user_birthday_list);
            MessengerResolver::resolve()->post($user, $message);
        }
    }

    public static function add(): void {
        $updates = MessengerResolver::resolve()->getUpdates();

        foreach ($updates as $update) {
            $update_portions = explode('.', $update->text);
            $bday_name = $update_portions[0];
            $bday_date = $update_portions[1] ?? '';

            if (empty($bday_name) || empty($bday_date)) {
                throw new \Exception('Birthday service `add` got unexpected update message');
            }

            BirthdayRepositoryResolver::resolve()
                ->create($update->user_uid, $bday_name, Clock::at($bday_date));
        }
    }

}
