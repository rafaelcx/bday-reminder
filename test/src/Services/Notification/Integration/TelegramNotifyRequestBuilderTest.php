<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration;

use App\Repository\Birthday\Birthday;
use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\TelegramNotifyRequestBuilder;
use App\Services\Notification\NotificationException;
use Test\CustomTestCase;

class TelegramNotifyRequestBuilderTest extends CustomTestCase {

    public function testBuilder(): void {
        $user = $this->createFakeUser();
        $birthday_1 = $this->createFakeBirthday($user);
        $birthday_2 = $this->createFakeBirthday($user);

        // Mocking valid credentials
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);
        
        // Mocking valid user configs
        $user_config_name = 'telegram-chat-id';
        $user_config_value = 'value';
        UserConfigRepositoryResolver::resolve()->create($user->uid, $user_config_name, $user_config_value);
        
        $request = TelegramNotifyRequestBuilder::build($user, ...[$birthday_1, $birthday_2]);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.telegram.org/botsome_token/sendMessage', (string) $request->getUri());
        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $this->assertBuiltRequestBody((string) $request->getBody());
    }

    public function testBuider_WhenMissingCredentials_ShouldThrow(): void {
        $user = $this->createFakeUser();
        $birthday = $this->createFakeBirthday($user);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification request build error: ');
        TelegramNotifyRequestBuilder::build($user, ...[$birthday]);
    }

    public function testBuilder_WhenMissingUserConfig_ShouldThrown(): void {
        $user = $this->createFakeUser();
        $birthday_1 = $this->createFakeBirthday($user);
        $birthday_2 = $this->createFakeBirthday($user);

        // Mocking valid credentials
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);
        
        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification request build error: ');
        TelegramNotifyRequestBuilder::build($user, ...[$birthday_1, $birthday_2]);
    }

    private function assertBuiltRequestBody(string $request_body): void {
        $this->assertStringContainsString('chat_id=value', $request_body);
        $this->assertStringContainsString('text=Hello', $request_body);
        $this->assertStringContainsString('parse_mode=Markdown', $request_body);
    }

    private function createFakeUser(): User {
        return new User(
            uid: 'uid',
            name: 'John Doe',
            created_at: new \DateTime(),
        );
    }

    private function createFakeBirthday(User $user): Birthday {
        return new Birthday(
            uid: uniqid(),
            user_uid: $user->uid,
            name: 'John Doe',
            date: new \DateTime(),
            created_at: new \DateTime(),
        );
    }
}
