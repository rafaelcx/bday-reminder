<?php

declare(strict_types=1);

namespace Test\Support\Repository\Birthday;

use App\Repository\Birthday\BirthdayRepositoryResolver;

class BirthdayRepositoryResolverForTests extends BirthdayRepositoryResolver {

    public static function reset(): void {
        parent::$instance = null;
    }

}
