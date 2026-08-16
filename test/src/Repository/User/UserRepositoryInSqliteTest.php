<?php

declare(strict_types=1);

namespace Test\Src\Repository\User;

use App\Repository\User\UserRepositoryInSqlite;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\DbCustomTestCase;

class UserRepositoryInSqliteTest extends DbCustomTestCase {

    private UserRepositoryInSqlite $user_repository;

    #[Before]
    public function prepareUserRepository(): void {
        $this->user_repository = new UserRepositoryInSqlite();
    }

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testRepository_CreateAndFindAll(): void {
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

    public function testRepository_FindAll_OnEmpty(): void {
        $persisted_users = $this->user_repository->findAll();
        $this->assertSame([], $persisted_users);
    }

}
