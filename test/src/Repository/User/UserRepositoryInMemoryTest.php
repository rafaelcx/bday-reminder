<?php

declare(strict_types=1);

namespace Test\Repository\User;

use App\Repository\User\UserRepositoryInMemory;
use Test\CustomTestCase;

class UserRepositoryInMemoryTest extends CustomTestCase {

    /** @before */
    public function resetRepositoryForTests(): void {
        $user_repository = new UserRepositoryInMemory();
        $user_repository::$user_list = [];
    }

    public function testRepository_Create(): void {
        $user_repository = new UserRepositoryInMemory();
        $user_repository->create('name_1');
        $user_repository->create('name_2');
        
        $this->assertCount(2, $user_repository::$user_list);

        $persisted_user_2 = array_pop($user_repository::$user_list);
        $persisted_user_1 = array_pop($user_repository::$user_list);

        $this->assertSame('name_1', $persisted_user_1['name']);
        $this->assertSame('name_2', $persisted_user_2['name']);
    }

    public function testRepository_FindAll(): void {
        $user_repository = new UserRepositoryInMemory();
        $user_repository->create('name_1');
        $user_repository->create('name_2');

        $persisted_users = $user_repository->findAll();
        $this->assertCount(2, $persisted_users);

        $persisted_user_2 = array_pop($persisted_users);
        $persisted_user_1 = array_pop($persisted_users);

        $this->assertSame('name_1', $persisted_user_1->name);
        $this->assertSame('name_2', $persisted_user_2->name);
    }

}
