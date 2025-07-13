<?php

declare(strict_types=1);

namespace App\Repository\UserConfig;

use App\Utils\StaticScope;

class UserConfigRepositoryResolver {

    public static function resolve(): UserConfigRepository {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): UserConfigRepository {
        return new UserConfigRepositoryInFile();
    }

}
