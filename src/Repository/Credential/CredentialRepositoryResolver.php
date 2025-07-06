<?php

declare(strict_types=1);

namespace App\Repository\Credential;

use App\Utils\StaticScope;

class CredentialRepositoryResolver {

    public static function resolve(): CredentialRepository {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): CredentialRepository {
        return new CredentialRepositoryInFile();
    }

}
