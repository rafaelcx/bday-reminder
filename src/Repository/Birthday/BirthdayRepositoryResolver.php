<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

use App\Utils\StaticScope;

class BirthdayRepositoryResolver {

    public static function resolve(): BirthdayRepository {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): BirthdayRepository {
        return new BirthdayRepositoryInFile();
    }

}
