<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Notify;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Utils\Clock;

class TelegramNotifyRequestMessage {
    
    public static function build(User $user, Birthday ...$birthdays): string {
        $message_lines = [];
        $user_name = $user->name;

        if (empty($birthdays)) {
            return <<<TXT
            Hello {$user_name},

            🙁 There are no birthdays coming up in the next 30 days.

            ❌ Don't be so anti social, go out there and make new friends!
            TXT;
        }

        $message_lines[] = "Hello {$user_name},";
        $message_lines[] = '';
        $message_lines[] = "Here are the birthdays coming up in the next 30 days:";
        $message_lines[] = '';

        $today = Clock::at(Clock::now()->format('Y-m-d'));

        foreach ($birthdays as $b) {
            $birthday_this_year = Clock::at(sprintf(
                '%s-%s-%s',
                $today->format('Y'),
                $b->date->format('m'),
                $b->date->format('d')
            ));

            $has_already_celebrated_birthday_this_year = $birthday_this_year->isBefore($today);

            $next_birthday_as_ydm = $has_already_celebrated_birthday_this_year
                ? Clock::at(sprintf('%s-%s-%s', $today->format('Y'), $b->date->format('m'), $b->date->format('d')))->plusYears(1)
                : Clock::at(sprintf('%s-%s-%s', $today->format('Y'), $b->date->format('m'), $b->date->format('d')));

            $days_until = (int) $today->diff($next_birthday_as_ydm)->d;

            $name = $b->name;

            $birthday_as_ymd = Clock::at($b->date->format('Y-m-d'));
            $birthday_as_md = Clock::at($b->date->format('Y-m-d'));
            $turning_age = $birthday_as_ymd->diff($next_birthday_as_ydm)->y;

            if ($days_until === 0) {
                $message_lines[] = "🎉 It's {$name}'s birthday today!";
                $message_lines[] = "🥳 Turns {$turning_age}";
                $message_lines[] = '';
            } elseif ($days_until === 1) {
                $message_lines[] = "🎈 Tomorrow: {$name}!";
                $message_lines[] = "🎂 Turns {$turning_age}! (📅 {$birthday_as_md->format('m/d')})";
                $message_lines[] = '';
            } else {
                $message_lines[] = "👶 {$name}";
                $message_lines[] = "🎂 Turns {$turning_age} in {$days_until} days (📅 {$birthday_as_md->format('m/d')})";
                $message_lines[] = '';
            }
        }

        $message_lines[] = "🎁 Don't forget to send your love!";

        return implode("\n", $message_lines);
    }

}
