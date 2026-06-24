<?php

declare(strict_types=1);

namespace App\Services\Birthday;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Interaction\Interactor;
use App\Services\Messenger\MessengerResolver;
use App\Services\Notification\Notifier;
use App\Utils\Clock;

class BirthdayService implements Notifier, Interactor {

    public function notify(): void {
        $user_list = UserRepositoryResolver::resolve()->findAll();
        
        foreach ($user_list as $user) {
            $user_birthday_list = BirthdayRepositoryResolver::resolve()
                ->findByUserUidInTheNextDays($user->uid, 30);

            $message = BirthdayServiceMessage::build($user, ...$user_birthday_list);
            MessengerResolver::resolve()->post($user, $message);
        }
    }

    public function processInteractions(): void {
        $updates = MessengerResolver::resolve()->getUpdates();

        foreach ($updates as $update) {
            $parts = str_getcsv($update->text, ' ', '"', '\\');
            
            $service = $parts[0] ?? '';
            $command = $parts[1] ?? '';

            if ($service !== 'bday' || $command !== 'add') {
                continue;
            }

            $name = $parts[2] ?? '';
            $bday = $parts[3] ?? '';

            if (empty($name) || empty($bday)) {
                throw new \Exception('Birthday service `add` got unexpected params');
            }

            BirthdayRepositoryResolver::resolve()
                ->create($update->user_uid, $name, Clock::at($bday));
        }
    }

}
