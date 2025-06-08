<?php

declare(strict_types=1);

namespace Test\Repository\Birthday;

use App\Repository\Birthday\BirthdayRepositoryInFile;
use Test\CustomTestCase;

class BirthdayRepositoryInFileTest extends CustomTestCase {

    private BirthdayRepositoryInFile $birthday_repository;

    /** @before */
    public function prepareBirthdayRepositoryForTests(): void {
        $this->birthday_repository = new BirthdayRepositoryInFile();
    }

    public function testRepository_CreateAndFindByUserUid(): void {
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

}
