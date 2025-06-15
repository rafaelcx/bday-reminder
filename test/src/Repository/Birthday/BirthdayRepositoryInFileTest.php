<?php

declare(strict_types=1);

namespace Test\Src\Repository\Birthday;

use App\Repository\Birthday\BirthdayRepositoryInFile;
use Test\CustomTestCase;

class BirthdayRepositoryInFileTest extends CustomTestCase {

    private BirthdayRepositoryInFile $birthday_repository;

    /** @before */
    public function prepareBirthdayRepositoryForTests(): void {
        $this->birthday_repository = new BirthdayRepositoryInFile();
    }

    public function testRepository_Create_OnFreshFile(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', new \DateTime('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', new \DateTime('1996-11-30'));
        $this->birthday_repository->create('user_uid_2', 'name_3', new \DateTime('1995-11-30'));
        $this->birthday_repository->create('user_uid_2', 'name_4', new \DateTime('1996-11-30'));

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $persisted_bday_2 = array_pop($persisted_bdays);
        $persisted_bday_1 = array_pop($persisted_bdays);

        $this->assertSame('name_1', $persisted_bday_1->name);
        $this->assertSame('name_2', $persisted_bday_2->name);

        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_2');
        $persisted_bday_2 = array_pop($persisted_bdays);
        $persisted_bday_1 = array_pop($persisted_bdays);

        $this->assertSame('name_3', $persisted_bday_1->name);
        $this->assertSame('name_4', $persisted_bday_2->name);
    }

    public function testRepository_FindByUserUid_OnFreshFile(): void {
        $persisted_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $this->assertSame([], $persisted_bdays);
    }

    public function testRepository_Update(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', new \DateTime('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', new \DateTime('2000-12-01'));
        $this->birthday_repository->create('user_uid_2', 'name_3', new \DateTime('1970-10-15'));

        $all_bdays_from_user_one = $this->birthday_repository->findByUserUid('user_uid_1');
        $all_bdays_from_user_two = $this->birthday_repository->findByUserUid('user_uid_2');
        
        $first_bday = $all_bdays_from_user_one[0];
        $this->birthday_repository->update($first_bday->uid, 'new_name', new \DateTime('2500-01-01'));

        $all_bdays_from_user_one = $this->birthday_repository->findByUserUid('user_uid_1');
        $all_bdays_from_user_two = $this->birthday_repository->findByUserUid('user_uid_2');

        $this->assertCount(2, $all_bdays_from_user_one);
        $this->assertCount(1, $all_bdays_from_user_two);
        $this->assertSame('new_name', $all_bdays_from_user_one[0]->name);
        $this->assertSame('name_2', $all_bdays_from_user_one[1]->name);
        $this->assertSame('name_3', $all_bdays_from_user_two[2]->name);
    }

    public function testRepository_Delete(): void {
        $this->birthday_repository->create('user_uid_1', 'name_1', new \DateTime('1995-11-30'));
        $this->birthday_repository->create('user_uid_1', 'name_2', new \DateTime('2000-12-01'));

        $all_bdays = $this->birthday_repository->findByUserUid('user_uid_1');
        $first_bday = $all_bdays[0];

        $this->birthday_repository->delete($first_bday->uid);
        $all_bdays = $this->birthday_repository->findByUserUid('user_uid_1');

        $this->assertCount(1, $all_bdays);
        $this->assertSame('name_2', $all_bdays[0]->name);
    }

}
