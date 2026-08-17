<?php

declare(strict_types=1);

namespace Test\Src\Repository\Birthday;

use App\Repository\Birthday\BirthdayRepositoryInSqlite;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\DbCustomTestCase;

class BirthdayRepositoryInSqliteTest extends DbCustomTestCase {

    private BirthdayRepositoryInSqlite $birthday_repository;

    #[Before]
    public function prepareBirthdayRepositoryForTests(): void {
        $this->birthday_repository = new BirthdayRepositoryInSqlite();
    }

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testRepository_CreateAndFindByUserUid(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', Clock::at('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', Clock::at('1996-11-30'));
        $this->birthday_repository->create('user_uid_2', 'name_3', Clock::at('1995-11-30'));
        $this->birthday_repository->create('user_uid_2', 'name_4', Clock::at('1996-11-30'));

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');

        $this->assertCount(2, $persisted_bdays);
        $this->assertNotEmpty($persisted_bdays[0]->uid);
        $this->assertSame('user_uid_1', $persisted_bdays[0]->user_uid);
        $this->assertSame('name_1', $persisted_bdays[0]->name);
        $this->assertSame('1995-11-30', $persisted_bdays[0]->date->asDateString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_bdays[0]->created_at->asDateTimeString());

        $this->assertNotEmpty($persisted_bdays[1]->uid);
        $this->assertSame('user_uid_1', $persisted_bdays[1]->user_uid);
        $this->assertSame('name_2', $persisted_bdays[1]->name);
        $this->assertSame('1996-11-30', $persisted_bdays[1]->date->asDateString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_bdays[1]->created_at->asDateTimeString());
    }

    public function testRepository_Update(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', Clock::at('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', Clock::at('2000-12-01'));
        $this->birthday_repository->create('user_uid_2', 'name_3', Clock::at('1970-10-15'));

        $all_bdays_from_user_one = $this->birthday_repository->findByUserUid('user_uid_1');
        $first_bday = $all_bdays_from_user_one[0];
        $this->birthday_repository->update($first_bday->uid, 'new_name', Clock::at('2500-01-01'));

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $this->assertCount(2, $persisted_bdays);
        $this->assertSame('new_name', $persisted_bdays[0]->name);
        $this->assertSame('2500-01-01', $persisted_bdays[0]->date->asDateString());
        $this->assertSame('name_2', $persisted_bdays[1]->name);
        $this->assertSame('2000-12-01', $persisted_bdays[1]->date->asDateString());
    }

    public function testRepository_Delete(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', Clock::at('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', Clock::at('2000-12-01'));

        $all_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $first_bday = $all_bdays[0];

        $this->birthday_repository->delete($first_bday->uid);
        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');

        $this->assertCount(1, $persisted_bdays);
        $this->assertSame('name_2', $persisted_bdays[0]->name);
        $this->assertSame('2000-12-01', $persisted_bdays[0]->date->asDateString());
    }

    public function testRepository_FindByUserUidInTheNextDays(): void {
        $this->birthday_repository->create('user_uid_1', 'today_birthday', Clock::at('1990-01-01'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_5_days', Clock::at('1995-01-06'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_30_days', Clock::at('1990-01-31'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_31_days', Clock::at('1990-02-01'));
        $this->birthday_repository->create('user_uid_1', 'past_bday', Clock::at('1990-12-25'));

        $relevant_bdays = $this->birthday_repository->findByUserUidInTheNextDays('user_uid_1', 30);

        $this->assertCount(3, $relevant_bdays);
        $birthday_names = array_map(fn($b) => $b->name, $relevant_bdays);
        $this->assertContains('today_birthday', $birthday_names);
        $this->assertContains('bday_in_5_days', $birthday_names);
        $this->assertContains('bday_in_30_days', $birthday_names);
        $this->assertNotContains('bday_in_31_days', $birthday_names);
        $this->assertNotContains('past_bday', $birthday_names);
    }
}
