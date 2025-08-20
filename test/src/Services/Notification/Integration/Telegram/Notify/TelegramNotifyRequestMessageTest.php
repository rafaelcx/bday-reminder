<?php

declare(strict_types=1);

namespace Test\Src\Services\Notification\Integration\Telegram\Notify;

use App\Repository\Birthday\Birthday;
use App\Repository\User\User;
use App\Services\Notification\Integration\Telegram\Notify\TelegramNotifyRequestMessage;
use App\Utils\Clock;
use Test\CustomTestCase;

class TelegramNotifyRequestMessageTest extends CustomTestCase {
    
    private User $test_user;

    /** @before */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-07-20 12:00:00');
    }

    /** @before */
    protected function setUpUserForTests(): void {
        $this->test_user = new User(
            uid: 'user-123',
            name: 'Alice',
            created_at: Clock::now()
        );
    }

    public function testBuilder_ShouldReturnNoBirthdayMessageWhenEmpty(): void {
        $message = TelegramNotifyRequestMessage::build($this->test_user, ...[]);
        $expected_message = <<<TXT
        Hello Alice,

        🙁 There are no birthdays coming up in the next 30 days.

        ❌ Don't be so anti social, go out there and make new friends!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldFormatBirthdayForToday(): void {
        $today = Clock::at('2000-07-20 12:00:00');
        // $today = new \DateTimeImmutable('today - 25 years');
        $birthday = new Birthday(
            uid: 'b1',
            user_uid: $this->test_user->uid,
            name: 'John Doe',
            date: Clock::at($today->format('Y-m-d')),
            created_at: Clock::at('2021-01-01')
        );

        $message = TelegramNotifyRequestMessage::build($this->test_user, $birthday);
        $expected_message = <<<TXT
        Hello Alice,

        Here are the birthdays coming up in the next 30 days:

        🎉 It's John Doe's birthday today!
        🥳 Turns 25

        🎁 Don't forget to send your love!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldFormatBirthdayForTomorrow(): void {
        $dob = Clock::now()->plusDays(1)->minusYears(30);
        $birthday = new Birthday(
            uid: 'b2',
            user_uid: $this->test_user->uid,
            name: 'Maria Lopez',
            date: Clock::at($dob->format('Y-m-d')),
            created_at: Clock::at('2021-01-01')
        );

        $message = TelegramNotifyRequestMessage::build($this->test_user, $birthday);
        $expected_message = <<<TXT
        Hello Alice,

        Here are the birthdays coming up in the next 30 days:

        🎈 Tomorrow: Maria Lopez!
        🎂 Turns 30! (📅 07/21)

        🎁 Don't forget to send your love!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldFormatBirthdayInSomeDays(): void {
        $dob = Clock::now()->plusDays(7)->plusYears(40);
        $birthday = new Birthday(
            uid: 'b3',
            user_uid: $this->test_user->uid,
            name: 'Carlos',
            date: Clock::at($dob->format('Y-m-d')),
            created_at: Clock::at('2021-01-01')
        );

        $message = TelegramNotifyRequestMessage::build($this->test_user, $birthday);
        $expected_message = <<<TXT
        Hello Alice,

        Here are the birthdays coming up in the next 30 days:

        👶 Carlos
        🎂 Turns 40 in 7 days (📅 07/27)

        🎁 Don't forget to send your love!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_MultipleBirthdaysAreAllFormatted(): void {
        $today = Clock::now()->minusYears(20);
        $tomorrow = Clock::now()->plusDays(1)->minusYears(30);
        $in5days = Clock::now()->plusDays(5)->minusYears(35);

        $b1 = new Birthday('1', $this->test_user->uid, 'Ana', Clock::at($today->format('Y-m-d')), Clock::now());
        $b2 = new Birthday('2', $this->test_user->uid, 'Bob', Clock::at($tomorrow->format('Y-m-d')), Clock::now());
        $b3 = new Birthday('3', $this->test_user->uid, 'Clara', Clock::at($in5days->format('Y-m-d')), Clock::now());

        $message = TelegramNotifyRequestMessage::build($this->test_user, $b1, $b2, $b3);
        $expected_message = <<<TXT
        Hello Alice,

        Here are the birthdays coming up in the next 30 days:

        🎉 It's Ana's birthday today!
        🥳 Turns 20

        🎈 Tomorrow: Bob!
        🎂 Turns 30! (📅 07/21)

        👶 Clara
        🎂 Turns 35 in 5 days (📅 07/25)

        🎁 Don't forget to send your love!
        TXT;

        $this->assertSame($expected_message, $message);
    }

}
