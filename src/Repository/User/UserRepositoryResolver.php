<?php

declare(strict_types=1);

namespace App\Repository\User;

class UserRepositoryResolver {

    protected static ?UserRepository $instance = null;

    public static function resolve(): UserRepository {
        if (is_null(self::$instance)) {
            self::createInstance();
        }
        return self::$instance;
    }

    private static function createInstance(): void {
        self::$instance = new UserRepositoryInFile();
    }

}
