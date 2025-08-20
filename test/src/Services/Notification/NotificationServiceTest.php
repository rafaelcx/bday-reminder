<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification;

use App\Repository\Birthday\Birthday;
use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Notification\NotificationService;
use App\Utils\Clock;
use Test\CustomTestCase;
use Test\Support\Services\Notification\Integration\NotifierForTests;
use Test\Support\Services\Notification\Integration\NotifierResolverForTests;

class NotificationServiceTest extends CustomTestCase {

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
        $mock_notifier = new NotifierForTests();

        // Simulating each fetched update
        $execution_proof = '';
        $get_updates_behavior = function() use (&$execution_proof) {
             $execution_proof = 'Updates Fetched';
             return [];
        }; 
        $mock_notifier->setGetUpdatesBehavior($get_updates_behavior);
        
        NotifierResolverForTests::override($mock_notifier);

        NotificationService::add();

        $this->assertSame('Updates Fetched', $execution_proof);
    }

    private function createAndGetUser(string $user_name): User {
        $user_repo = UserRepositoryResolver::resolve();
        $user_repo->create($user_name);
        
        $all_users = $user_repo->findAll();
        $created_user = array_filter($all_users, fn(User $u) => $u->name === $user_name);
        return array_pop($created_user);
    }

    private function createBirthdayForUser(User $user, string $bday_name): void {
        BirthdayRepositoryResolver::resolve()->create($user->uid, $bday_name, Clock::now());
    }

}
