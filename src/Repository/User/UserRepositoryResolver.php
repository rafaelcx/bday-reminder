<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Utils\StaticScope;

class UserRepositoryResolver {

    public static function resolve(): UserRepository {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): UserRepository {
        return new UserRepositoryInFile();
    }

}
