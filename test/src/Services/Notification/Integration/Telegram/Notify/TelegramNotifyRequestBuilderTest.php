<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram\Notify;

use App\Repository\Birthday\Birthday;
use App\Repository\Credential\CredentialRepositoryResolver;
use App\Repository\User\User;
use App\Repository\UserConfig\UserConfigRepositoryResolver;
use App\Services\Notification\Integration\Telegram\Notify\TelegramNotifyRequestBuilder;
use App\Services\Notification\NotificationException;
use App\Utils\Clock;
use DateTime;
use Test\CustomTestCase;

class TelegramNotifyRequestBuilderTest extends CustomTestCase {

    /** @before */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-12-20 12:00:00');
    }

    public function testBuilder(): void {
        $user = $this->createFakeUser();
        $birthday_1 = $this->createFakeBirthday($user, 'Alice', '2025-12-21 12:00:00');
        $birthday_2 = $this->createFakeBirthday($user, 'Jhon', '2025-12-22 12:00:00');

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
        
        parse_str((string) $request->getBody(), $parsed_body);

        $this->assertSame('value', $parsed_body['chat_id']);
        $this->assertSame('Markdown', $parsed_body['parse_mode']);
        $this->assertStringContainsString('Hello John Doe,', $parsed_body['text']);
        $this->assertStringContainsString("Don't forget to send your love!", $parsed_body['text']);
    }

    public function testBuilder_BirthdaysOnPayloadShouldBeFilteredAndOrdered(): void {
        $user = $this->createFakeUser();
        $birthday_1 = $this->createFakeBirthday($user, 'Rafael', '1995-12-19');
        $birthday_2 = $this->createFakeBirthday($user, 'Aline', '1995-12-20');
        $birthday_3 = $this->createFakeBirthday($user, 'Marcelo', '1995-12-25');
        $birthday_4 = $this->createFakeBirthday($user, 'Silvia', '1995-01-01');
        $birthday_5 = $this->createFakeBirthday($user, 'Matheus', '1995-01-19');
        $birthday_6 = $this->createFakeBirthday($user, 'Emily', '1995-01-20');

        // Mocking valid credentials
        $credential_id = 'telegram-credential';
        $credential_data = '{"bot_token": "some_token"}';
        CredentialRepositoryResolver::resolve()->create($credential_id, $credential_data);
        
        // Mocking valid user configs
        $user_config_name = 'telegram-chat-id';
        $user_config_value = 'value';
        UserConfigRepositoryResolver::resolve()->create($user->uid, $user_config_name, $user_config_value);
        
        $request = TelegramNotifyRequestBuilder::build($user, ...[
            $birthday_1,
            $birthday_4,
            $birthday_3, 
            $birthday_5,
            $birthday_2,
            $birthday_6,
        ]);

        parse_str((string) $request->getBody(), $parsed_body);

        $expected_filtered_and_ordered_str = <<<TXT
        🎉 It's Aline's birthday today!
        🥳 Turns 30

        👶 Marcelo
        🎂 Turns 30 in 5 days (📅 12/25)

        👶 Silvia
        🎂 Turns 31 in 12 days (📅 01/01)

        👶 Matheus
        🎂 Turns 31 in 30 days (📅 01/19)
        TXT;

        $this->assertStringContainsString($expected_filtered_and_ordered_str, $parsed_body['text']);
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

    private function createFakeUser(): User {
        return new User(
            uid: 'uid',
            name: 'John Doe',
            created_at: new \DateTime(),
        );
    }

    private function createFakeBirthday(User $user, string $name = 'John Doe', string $date_time = 'now'): Birthday {
        return new Birthday(
            uid: uniqid(),
            user_uid: $user->uid,
            name: $name,
            date: Clock::at($date_time),
            created_at: Clock::now(),
        );
    }

}
