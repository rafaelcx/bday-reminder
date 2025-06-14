<?php

declare(strict_types=1);

namespace Test\Support\Repository\User;

use App\Repository\User\UserRepositoryResolver;

class UserRepositoryResolverForTests extends UserRepositoryResolver {

    public static function reset(): void {
        parent::$instance = null;
    }

}
