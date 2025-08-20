<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\TelegramNotifier;
use App\Services\Notification\NotificationException;
use App\Utils\Clock;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;
use Test\Support\Http\Client\HttpClientForTests;

class TelegramNotifierTest extends CustomTestCase {

    public function testNotifier_Notify(): void {
        $user = $this->createAndGetUser('user1');
        $bdays = $this->createAndGetBirthday($user, 'bday1');

        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        // Mocking valid response
        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"ok": true}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $notifier = new TelegramNotifier();
        $notifier->notify($user, ...$bdays);

        $last_sent_request = $mock_handler->getLastRequest();
        $this->assertNotNull($last_sent_request);
    }

    public function testNotifier_Notify_ShouldThrowOnRequestBuildingError(): void {
        $user = $this->createAndGetUser('user1');
        $bdays = $this->createAndGetBirthday($user, 'bday1');

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification request build error: Credential not find for id: telegram-credential');

        $notifier = new TelegramNotifier();
        $notifier->notify($user, ...$bdays);
    }

    public function testNotifier_Notify_ShouldThrowOnRequestDispatchError(): void {
        $user = $this->createAndGetUser('user1');
        $bdays = $this->createAndGetBirthday($user, 'bday1');

        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        // Mocking request dispatch error
        $mock_handler = new MockHandler();
        $mock_handler->append(new RequestException('Request error', new Request('GET', 'test')));
        HttpClientForTests::overrideHandler($mock_handler);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification error: External HTTP request failed: Request error');

        $notifier = new TelegramNotifier();
        $notifier->notify($user, ...$bdays);
    }

    public function testNotifier_Notify_ShouldThrowOnParsingError(): void {
        $user = $this->createAndGetUser('user1');
        $bdays = $this->createAndGetBirthday($user, 'bday1');

        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        // Mocking not valid response
        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], 'Hello, World'));
        HttpClientForTests::overrideHandler($mock_handler);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification response parsing error: could not parse');

        $notifier = new TelegramNotifier();
        $notifier->notify($user, ...$bdays);
    }

    public function testNotifier_GetUpdates(): void {
        $this->mockValidCredentials();

        // Mocking valid response
        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"result": []}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $notifier = new TelegramNotifier();
        $notifier->getUpdates();

        $last_sent_request = $mock_handler->getLastRequest();
        $this->assertNotNull($last_sent_request);
    }

    public function testNotifier_DeleteMessages(): void {
        $this->mockValidCredentials();

        // Mocking valid response
        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"ok": true}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $update = new \stdClass;
        $update->text = 'text';
        $update->chat_id = '123';
        $update->id = '123';

        $notifier = new TelegramNotifier();
        $notifier->deleteMessages([$update]);

        $last_sent_request = $mock_handler->getLastRequest();
        $this->assertNotNull($last_sent_request);
    }

    private function createAndGetUser(string $user_name): User {
        $user_repo = UserRepositoryResolver::resolve();
        $user_repo->create($user_name);
        
        $all_users = $user_repo->findAll();
        $created_user = array_filter($all_users, fn(User $u) => $u->name === $user_name);
        return array_pop($created_user);
    }

    private function createAndGetBirthday(User $user, string $birthday_name): array {
        $birthday_repo = BirthdayRepositoryResolver::resolve();
        $birthday_repo->create($user->uid, $birthday_name, Clock::now());
        return $birthday_repo->findByUserUid($user->uid);
    }

    private function mockValidCredentials(): void {
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);
    }

    private function mockValidUserConfigs(User $user): void {
        $user_config_name = 'telegram-chat-id';
        $user_config_value = 'value';
        UserConfigRepositoryResolver::resolve()->create($user->uid, $user_config_name, $user_config_value);
    }

}
