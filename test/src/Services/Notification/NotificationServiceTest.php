<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification;

use App\Repository\Birthday\Birthday;
use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\Updates\TelegramUpdate;
use App\Services\Notification\NotificationService;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\CustomTestCase;
use Test\Support\Services\Notification\Integration\NotifierForTests;
use Test\Support\Services\Notification\Integration\NotifierResolverForTests;

class NotificationServiceTest extends CustomTestCase {

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testService_Notify_ShouldNotifyAllBirthdaysFromAllUsers(): void {
        $user_1 = $this->createAndGetUser('user1');
        $user_2 = $this->createAndGetUser('user2');

        $this->createBirthdayForUser($user_1, 'bday1');
        $this->createBirthdayForUser($user_1, 'bday2');
        $this->createBirthdayForUser($user_2, 'bday3');
        $this->createBirthdayForUser($user_2, 'bday4');

        // Notifier behavior will be to concatenate all found user and birthday names into one string
        $execution_proof = '';
        $mock_notifier_behavior = function(User $user, array $birthdays) use (&$execution_proof) {
            $execution_proof .= $user->name;
            $execution_proof .= implode(array_map(fn(Birthday $b) => $b->name, $birthdays));
        };

        // Registering the simulated Notifier
        $mock_notifier = new NotifierForTests();
        $mock_notifier->setNotifyBehavior($mock_notifier_behavior);
        NotifierResolverForTests::override($mock_notifier);

        NotificationService::notify();

        $this->assertStringContainsString('user1', $execution_proof);
        $this->assertStringContainsString('user2', $execution_proof);
        $this->assertStringContainsString('bday1', $execution_proof);
        $this->assertStringContainsString('bday2', $execution_proof);
        $this->assertStringContainsString('bday3', $execution_proof);
        $this->assertStringContainsString('bday4', $execution_proof);
    }

    public function testService_Add_ShouldAddBirthdaysFromAllUsers(): void {
        $user_1 = $this->createAndGetUser('user1');
        $user_2 = $this->createAndGetUser('user2');

        UserConfigRepositoryResolver::resolve()->create($user_1->uid, 'telegram-chat-id', '1');
        UserConfigRepositoryResolver::resolve()->create($user_2->uid, 'telegram-chat-id', '2');
        
        $update_1 = new TelegramUpdate(
            id: '1',
            message_id: '10',
            user_uid: $user_1->uid,
            birhday_name: 'Name One',
            birthday_date: Clock::at('1995-11-30')
        );

        $update_2 = new TelegramUpdate(
            id: '2',
            message_id: '20',
            user_uid: $user_2->uid,
            birhday_name: 'Name Two',
            birthday_date: Clock::at('2000-01-01')
        );

        // Simulating each fetched update
        $get_updates_behavior = fn() => [$update_1, $update_2];

        $mock_notifier = new NotifierForTests();
        $mock_notifier->setGetUpdatesBehavior($get_updates_behavior);
        NotifierResolverForTests::override($mock_notifier);

        NotificationService::add();

        $user_1_birthdays = BirthdayRepositoryResolver::resolve()->findByUserUid($user_1->uid);
        $user_2_birthdays = BirthdayRepositoryResolver::resolve()->findByUserUid($user_2->uid);

        $this->assertCount(1, $user_1_birthdays);
        $this->assertCount(1, $user_2_birthdays);

        $user_1_birthday = array_pop($user_1_birthdays);
        $user_2_birthday = array_pop($user_2_birthdays);
        
        $this->assertSame('Name One', $user_1_birthday->name);
        $this->assertSame('Name Two', $user_2_birthday->name);
        $this->assertSame('1995-11-30', $user_1_birthday->date->asDateString());
        $this->assertSame('2000-01-01', $user_2_birthday->date->asDateString());
    }

    public function testService_Notify_ShouldOnlyNotifyBirthdaysInTheNext30Days(): void {
        $user = $this->createAndGetUser('user1');

        // Create birthdays at various distances
        $this->createBirthdayForUser($user, 'today_bday', Clock::at('1990-01-01'));
        $this->createBirthdayForUser($user, 'bday_in_10_days', Clock::at('1990-01-11'));
        $this->createBirthdayForUser($user, 'bday_in_30_days', Clock::at('1990-01-31'));
        $this->createBirthdayForUser($user, 'bday_in_32_days', Clock::at('1990-02-02'));
        $this->createBirthdayForUser($user, 'past_bday', Clock::at('1990-12-25'));

        // Track which birthdays were received in the notify call
        $received_birthdays = [];
        $mock_notifier_behavior = function(User $u, array $birthdays) use (&$received_birthdays) {
            foreach ($birthdays as $birthday) {
                $received_birthdays[] = $birthday->name;
            }
        };

        // Register the mock notifier
        $mock_notifier = new NotifierForTests();
        $mock_notifier->setNotifyBehavior($mock_notifier_behavior);
        NotifierResolverForTests::override($mock_notifier);

        NotificationService::notify();

        // Verify only birthdays in the next 30 days are included
        $this->assertContains('today_bday', $received_birthdays);
        $this->assertContains('bday_in_10_days', $received_birthdays);
        $this->assertContains('bday_in_30_days', $received_birthdays);
        $this->assertNotContains('bday_in_32_days', $received_birthdays);
        $this->assertNotContains('past_bday', $received_birthdays);
    }

    private function createAndGetUser(string $user_name): User {
        $user_repo = UserRepositoryResolver::resolve();
        $user_repo->create($user_name);
        
        $all_users = $user_repo->findAll();
        $created_user = array_filter($all_users, fn(User $u) => $u->name === $user_name);
        return array_pop($created_user);
    }

    private function createBirthdayForUser(User $user, string $bday_name, ?Clock $date = null): void {
        BirthdayRepositoryResolver::resolve()->create($user->uid, $bday_name, $date ?? Clock::now());
    }

}
