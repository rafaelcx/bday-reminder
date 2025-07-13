<?php

declare(strict_types=1);

namespace Test\Src\Repository\UserConfig;

use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryInFile;
use Test\CustomTestCase;

class UserConfigRepositoryInFileTest extends CustomTestCase {

    public function testRepository_Create(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');

        $found_user_config = $repository->findByUserUidAndName('user_uid', 'test_name');

        $this->assertSame('user_uid', $found_user_config->user_uid);
        $this->assertSame('test_name', $found_user_config->name);
        $this->assertSame('test_value', $found_user_config->value);
        $this->assertNotNull($found_user_config->created_at);
        $this->assertNotNull($found_user_config->updated_at);
    }

    public function testRepository_FindByUserUidAndName_WhenConfigDoesNotExistForUser(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');
        
        $this->expectException(UserConfigException::class);
        $this->expectExceptionMessage('Config not found for user with uid `other_user_uid` and name `test_name`');
        $repository->findByUserUidAndName('other_user_uid', 'test_name');
    }

    public function testRepository_FindByUserUidAndName_WhenConfigDoesNotExistWithName(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');

        $this->expectException(UserConfigException::class);
        $this->expectExceptionMessage('Config not found for user with uid `user_uid` and name `other_test_name`');
        $repository->findByUserUidAndName('user_uid', 'other_test_name');
    }



}
