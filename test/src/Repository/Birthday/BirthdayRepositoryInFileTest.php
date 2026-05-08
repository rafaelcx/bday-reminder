<?php

declare(strict_types=1);

namespace Test\Src\Repository\Birthday;

use App\Repository\Birthday\BirthdayRepositoryInFile;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\CustomTestCase;

class BirthdayRepositoryInFileTest extends CustomTestCase {

    private BirthdayRepositoryInFile $birthday_repository;

    #[Before]
    public function prepareBirthdayRepositoryForTests(): void {
        $this->birthday_repository = new BirthdayRepositoryInFile();
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

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_2');
        
        $this->assertNotEmpty($persisted_bdays[0]->uid);
        $this->assertSame('user_uid_2', $persisted_bdays[0]->user_uid);
        $this->assertSame('name_3', $persisted_bdays[0]->name);
        $this->assertSame('1995-11-30', $persisted_bdays[0]->date->asDateString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_bdays[0]->created_at->asDateTimeString());

        $this->assertNotEmpty($persisted_bdays[1]->uid);
        $this->assertSame('user_uid_2', $persisted_bdays[1]->user_uid);
        $this->assertSame('name_4', $persisted_bdays[1]->name);
        $this->assertSame('1996-11-30', $persisted_bdays[1]->date->asDateString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_bdays[1]->created_at->asDateTimeString());
    }

    public function testRepository_FindByUserUid_OnFreshFile(): void {
        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $this->assertSame([], $persisted_bdays);
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

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_2');
        $this->assertCount(1, $persisted_bdays);
        $this->assertSame('name_3', $persisted_bdays[0]->name);
        $this->assertSame('1970-10-15', $persisted_bdays[0]->date->asDateString());
    }

    public function testRepository_Delete(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', Clock::at('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', Clock::at('2000-12-01'));

        $all_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $first_bday = $all_bdays[0];

        $this->birthday_repository->delete($first_bday->uid);
        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');

        $this->assertCount(1, $persisted_bdays);
        $this->assertNotEmpty($persisted_bdays[0]->uid);
        $this->assertSame('user_uid_1', $persisted_bdays[0]->user_uid);
        $this->assertSame('name_2', $persisted_bdays[0]->name);
        $this->assertSame('2000-12-01', $persisted_bdays[0]->date->asDateString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_bdays[0]->created_at->asDateTimeString());
    }

    public function testRepository_FindByUserUidInTheNextDays_ShouldFilterBirthdaysInTheNextDays(): void {
        $this->birthday_repository->create('user_uid_1', 'today_birthday', Clock::at('1990-01-01'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_5_days', Clock::at('1995-01-06'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_30_days', Clock::at('1990-01-31'));
        $this->birthday_repository->create('user_uid_1', 'bday_in_31_days', Clock::at('1990-02-01'));
        $this->birthday_repository->create('user_uid_1', 'past_bday', Clock::at('1990-12-25'));

        $relevant_bdays = $this->birthday_repository->findByUserUidInTheNextDays('user_uid_1', 30);

        // Should include today, bday in 5 days, and bday in 30 days
        // Should NOT include bday in 31 days or past birthdays
        $this->assertCount(3, $relevant_bdays);
        
        $birthday_names = array_map(fn($b) => $b->name, $relevant_bdays);
        $this->assertContains('today_birthday', $birthday_names);
        $this->assertContains('bday_in_5_days', $birthday_names);
        $this->assertContains('bday_in_30_days', $birthday_names);
        $this->assertNotContains('bday_in_31_days', $birthday_names);
        $this->assertNotContains('past_bday', $birthday_names);
    }

    public function testRepository_FindByUserUidInTheNextDays_ShouldNotIncludePastBirthdaysEvenIfTheyHappenedEarlierThisYear(): void {
        Clock::freeze('2025-01-15 12:00:00');
        
        $this->birthday_repository->create('user_uid_1', 'past_bday_this_year', Clock::at('1990-01-10'));
        $this->birthday_repository->create('user_uid_1', 'upcoming_bday_this_year', Clock::at('1990-02-01'));

        $relevant_bdays = $this->birthday_repository->findByUserUidInTheNextDays('user_uid_1', 30);

        // Only the upcoming birthday should be included
        $this->assertCount(1, $relevant_bdays);
        $this->assertSame('upcoming_bday_this_year', $relevant_bdays[0]->name);
    }

    public function testRepository_FindByUserUidInTheNextDays_ShouldIncludeBirthdaysFromNextYear(): void {
        Clock::freeze('2025-12-15 12:00:00');
        
        $this->birthday_repository->create('user_uid_1', 'upcoming_this_year', Clock::at('1990-12-20'));
        $this->birthday_repository->create('user_uid_1', 'next_year_upcoming', Clock::at('1990-01-10'));

        $relevant_bdays = $this->birthday_repository->findByUserUidInTheNextDays('user_uid_1', 30);

        // Should include both upcoming this year and next year
        $this->assertCount(2, $relevant_bdays);
        
        $birthday_names = array_map(fn($b) => $b->name, $relevant_bdays);
        $this->assertContains('upcoming_this_year', $birthday_names);
        $this->assertContains('next_year_upcoming', $birthday_names);
    }

}
