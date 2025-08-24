<?php

declare(strict_types=1);

namespace Test\Src\Repository\UserConfig;

use App\Repository\UserConfig\UserConfigException;
use App\Repository\UserConfig\UserConfigRepositoryInFile;
use App\Utils\Clock;
use Test\CustomTestCase;

class UserConfigRepositoryInFileTest extends CustomTestCase {

    /**
     * @before
     */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testRepository_CreateAndFind(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');

        $found_user_config = $repository->findByUserUidAndName('user_uid', 'test_name');

        $this->assertSame('user_uid', $found_user_config->user_uid);
        $this->assertSame('test_name', $found_user_config->name);
        $this->assertSame('test_value', $found_user_config->value);
        $this->assertSame('2025-01-01 12:00:00', $found_user_config->created_at->asDateTimeString());
        $this->assertSame('2025-01-01 12:00:00', $found_user_config->updated_at->asDateTimeString());

        $found_user_config = $repository->findByNameAndValue('test_name', 'test_value');

        $this->assertSame('user_uid', $found_user_config->user_uid);
        $this->assertSame('test_name', $found_user_config->name);
        $this->assertSame('test_value', $found_user_config->value);
        $this->assertSame('2025-01-01 12:00:00', $found_user_config->created_at->asDateTimeString());
        $this->assertSame('2025-01-01 12:00:00', $found_user_config->updated_at->asDateTimeString());
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

    public function testRepository_FindByNameAndValue_WhenConfigDoesNotExistForName(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');

        $this->expectException(UserConfigException::class);
        $this->expectExceptionMessage('Config not found for user with name `other_name` and value `test_value`');
        $repository->findByNameAndValue('other_name', 'test_value');
    }

    public function testRepository_FindByNameAndValue_WhenConfigDoesNotExistForValue(): void {
        $repository = new UserConfigRepositoryInFile();
        $repository->create('user_uid', 'test_name', 'test_value');

        $this->expectException(UserConfigException::class);
        $this->expectExceptionMessage('Config not found for user with name `test_name` and value `other_value`');
        $repository->findByNameAndValue('test_name', 'other_value');
    }

}
