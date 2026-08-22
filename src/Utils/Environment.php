<?php

declare(strict_types=1);

namespace App\Utils;

class Environment {

    public static function getCurrent(): EnvironmentType {
        return StaticScope::getOrCreate(self::class, 'env', fn() => EnvironmentType::PROD);
    }

}
