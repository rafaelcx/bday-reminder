<?php

declare(strict_types=1);

namespace Test\Support\Repository\User;

use App\Repository\User\UserRepository;
use App\Repository\User\UserRepositoryResolver;

class UserRepositoryResolverForTests extends UserRepositoryResolver {

    public static function override(UserRepository $instance): void {
        self::$instance = $instance;
    }

}
