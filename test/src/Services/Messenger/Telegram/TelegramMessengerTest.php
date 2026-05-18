<?php

declare(strict_types=1);

namespace Test\Src\Services\Messenger\Telegram;

use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Messenger\Telegram\TelegramMessenger;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Test\CustomTestCase;
use Test\Support\Http\Client\HttpClientForTests;

class TelegramMessengerTest extends CustomTestCase {

    public function testMessenger_Post(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"ok": true}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $messenger = new TelegramMessenger();
        $messenger->post($user, 'Test message');

        $last_sent_request = $mock_handler->getLastRequest();
        $this->assertNotNull($last_sent_request, 'Failed to send request');

        $this->assertSame('POST', $last_sent_request->getMethod());
        $this->assertSame('https://api.telegram.org/botsome_token/sendMessage', (string) $last_sent_request->getUri());
        $this->assertSame('application/x-www-form-urlencoded', $last_sent_request->getHeaderLine('Content-Type'));
        
        $sent_body = (string) $last_sent_request->getBody();
        $expected_body = 'chat_id=42&text=Test+message&parse_mode=Markdown';
        $this->assertSame($expected_body, $sent_body);
    }

    public function testMessenger_Post_ShouldThrowOnRequestBuildingError(): void {
        $user = $this->createAndGetUser('user1');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification request build error: Credential not find for id: telegram-credential');

        $messenger = new TelegramMessenger();
        $messenger->post($user, 'Test message');
    }

    public function testMessenger_Post_ShouldThrowOnRequestDispatchError(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $mock_handler->append(new RequestException('Request error', new Request('POST', 'test')));
        HttpClientForTests::overrideHandler($mock_handler);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Messenger error: External HTTP request failed: Request error');

        $messenger = new TelegramMessenger();
        $messenger->post($user, 'Test message');
    }

    public function testMessgenger_Post_ShouldThrowOnMalformedJson(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{malformed_json}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification response parsing error: could not parse');

        $messenger = new TelegramMessenger();
        $messenger->post($user, 'Test message');
    }

    public function testMessenger_Post_ShouldThrowOnNotOkResponse(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"ok": false}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Notification response parsing error: Unknown error');

        $messenger = new TelegramMessenger();
        $messenger->post($user, 'Test message');
    }

    public function testMessenger_GetUpdates_WhenThereAreUpdates(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $get_update_response_body = '{
            "result": [
                {
                    "update_id": 42,
                    "message": {
                        "text": "name1.01-01-1995",
                        "chat": { "id": 42 },
                        "message_id": 42
                    }
                },
                {
                    "update_id": 84,
                    "message": {
                        "text": "name2.30-12-1990",
                        "chat": { "id": 42 },
                        "message_id": 84
                    }
                }
            ]
        }';
        $mock_handler->append(new Response(200, [], $get_update_response_body)); // Mock first valid get update response
        $mock_handler->append(new Response(200, [], '{"result": []}')); // Mock second, offset updater, valid response
        $mock_handler->append(new Response(200, [], '{"ok": true}')); // Mock valid delete message response
        HttpClientForTests::overrideHandler($mock_handler);

        $messenger = new TelegramMessenger();
        $updates = $messenger->getUpdates($user);

        $this->assertCount(2, $updates);

        // TODO: Assertions against all request objects that were sent.
    }

    public function testMessenger_GetUpdates_WhenThereIsNoUpdates(): void {
        $user = $this->createAndGetUser('user1');
        $this->mockValidCredentials();
        $this->mockValidUserConfigs($user);

        $mock_handler = new MockHandler();
        $mock_handler->append(new Response(200, [], '{"result": []}'));
        HttpClientForTests::overrideHandler($mock_handler);

        $messenger = new TelegramMessenger();
        $updates = $messenger->getUpdates($user);
        $this->assertCount(0, $updates);
    }

    private function createAndGetUser(string $user_name): User {
        $user_repo = UserRepositoryResolver::resolve();
        $user_repo->create($user_name);
        $all_users = $user_repo->findAll();
        $created_user = array_filter($all_users, fn(User $u) => $u->name === $user_name);
        return array_pop($created_user);
    }

    private function mockValidCredentials(): void {
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);
    }

    private function mockValidUserConfigs(User $user): void {
        $user_config_name = 'telegram-chat-id';
        $user_config_value = '42';
        UserConfigRepositoryResolver::resolve()->create($user->uid, $user_config_name, $user_config_value);
    }

}
