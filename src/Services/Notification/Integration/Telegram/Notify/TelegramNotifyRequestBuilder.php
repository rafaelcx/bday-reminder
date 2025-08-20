<?php

declare(strict_types=1);

namespace App\Services\Notification\Integration\Telegram\Notify;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\TelegramCredentials;
use App\Services\Notification\NotificationException;
use App\Utils\Clock;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class TelegramNotifyRequestBuilder {

    public static function build(User $user, Birthday ...$birthdays): RequestInterface {
        $uri = self::buildRequestUri();
        $chat_id = self::resolveChatIdFromUserUid($user->uid);
        $message = self::formatBirthdayMessage($user, ...$birthdays);

        if ($chat_id === null) {
            throw new NotificationException('No Telegram chat ID found for user ' . $user->uid);
        }

        $body = http_build_query([
            'chat_id'    => $chat_id,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ]);

        return new Request(
            method: 'POST',
            uri: $uri,
            headers: ['Content-Type' => 'application/x-www-form-urlencoded'],
            body: $body
        );
    }

    private static function buildRequestUri(): string {
        try {
            $bot_token = TelegramCredentials::getBotToken();
        } catch (\Exception $e) {
            throw new NotificationException('Notification request build error: ' . $e->getMessage());
        }
        return "https://api.telegram.org/bot{$bot_token}/sendMessage";
    }

    private static function resolveChatIdFromUserUid(string $user_uid): string {
        $user_cfg_name = 'telegram-chat-id';
        try {
            $user_cfg = UserConfigRepositoryResolver::resolve()->findByUserUidAndName($user_uid, $user_cfg_name);
        } catch (UserConfigException $e) {
            throw new NotificationException('Notification request build error: ' . $e->getMessage());
        }
        return $user_cfg->value;
    }

    private static function formatBirthdayMessage(User $user, Birthday ...$birthdays): string {
        // TODO: Add a method at BirthdayRepository to fetch users with a date filter.
        // TelegramNotifier should receive already filtered birthdays from NotificationService

        $relevant_birthdays = array_filter($birthdays, self::filterForBirthdaysInTheNext30Days(...));
        uasort($relevant_birthdays, self::sortBirthdays(...));
    
        return TelegramNotifyRequestMessage::build($user, ...$relevant_birthdays);
    }

    private static function filterForBirthdaysInTheNext30Days(Birthday $b): bool {
        $today_as_md = Clock::at(Clock::now()->format('m/d'));
        $today_as_ymd = Clock::at(Clock::now()->format('Y-m-d'));
        $birthday_as_md = Clock::at($b->date->format('m/d'));

        $birthday_is_today = $today_as_md->asDateString() === $birthday_as_md->asDateString();
        if ($birthday_is_today) {
            return true;
        }

        $has_already_celebrated_birthday_this_year = $birthday_as_md->isBefore($today_as_md);

        $next_birthday_as_ydm = $has_already_celebrated_birthday_this_year
            ? Clock::at($b->date->format('m/d'))->plusYears(1)
            : Clock::at($b->date->format('m/d'));

        return true
            && $next_birthday_as_ydm->isAfter($today_as_ymd) 
            && $next_birthday_as_ydm->isBefore($today_as_ymd->plusDays(31));
    }

    private static function sortBirthdays(Birthday $b1, Birthday $b2): int {
        $today = Clock::at(Clock::now()->format('Y-m-d'));

        $next_b1 = Clock::at(sprintf('%s-%s-%s', $today->format('Y'), $b1->date->format('m'), $b1->date->format('d')), 'Y-m-d');
        $next_b2 = Clock::at(sprintf('%s-%s-%s', $today->format('Y'), $b2->date->format('m'), $b2->date->format('d')), 'Y-m-d');

        if ($next_b1->getTimestamp() < $today->getTimestamp()) {
            $next_b1 = $next_b1->plusYears(1);
        }

        if ($next_b2->getTimestamp() < $today->getTimestamp()) {
            $next_b2 = $next_b2->plusYears(1);
        }

        return $next_b1 <=> $next_b2;
    }

}
