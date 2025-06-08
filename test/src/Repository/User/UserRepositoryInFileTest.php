<?php

declare(strict_types=1);

namespace Test\Repository\User;

use App\Repository\User\UserRepositoryInFile;
use Test\CustomTestCase;

class UserRepositoryInFileTest extends CustomTestCase {

    private UserRepositoryInFile $user_repository;

    /** @before */
    public function prepareUserRepositoryForTests(): void {
        $this->user_repository = new UserRepositoryInFile();
    }

    public function testRepository_CreateAndFindAll(): void {
        $this->user_repository->create('name_1');
        $this->user_repository->create('name_2');

        $persisted_users = $this->user_repository->findAll();
        $persisted_user_2 = array_pop($persisted_users);
        $persisted_user_1 = array_pop($persisted_users);

        $this->assertSame('name_1', $persisted_user_1->name);
        $this->assertSame('name_2', $persisted_user_2->name);
    }

}
