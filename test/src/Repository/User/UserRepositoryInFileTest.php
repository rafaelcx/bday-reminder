<?php

declare(strict_types=1);

namespace Test\Src\Repository\User;

use App\Repository\User\UserRepositoryInFile;
use App\Utils\Clock;
use Test\CustomTestCase;

class UserRepositoryInFileTest extends CustomTestCase {

    private UserRepositoryInFile $user_repository;

    /** @before */
    public function prepareUserRepositoryForTests(): void {
        $this->user_repository = new UserRepositoryInFile();
    }

    /** @before */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testRepository_CreateAndFindAll_OnFreshFile(): void {
        $this->user_repository->create('name_1');
        $this->user_repository->create('name_2');

        $persisted_users = $this->user_repository->findAll();

        $this->assertNotEmpty($persisted_users[0]->uid);
        $this->assertNotEmpty($persisted_users[1]->uid);
        $this->assertSame('name_1', $persisted_users[0]->name);
        $this->assertSame('name_2', $persisted_users[1]->name);
        $this->assertSame('2025-01-01 12:00:00', $persisted_users[0]->created_at->asDateTimeString());
        $this->assertSame('2025-01-01 12:00:00', $persisted_users[1]->created_at->asDateTimeString());
    }

    public function testRepository_FindAll_OnFreshFile(): void {
        $persisted_users = $this->user_repository->findAll();
        $this->assertSame([], $persisted_users);
    }

}
