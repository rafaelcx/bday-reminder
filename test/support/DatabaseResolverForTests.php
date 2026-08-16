<?php

declare(strict_types=1);

namespace Test\Support;

use App\Storage\Database\DatabaseResolver;
use App\Utils\StaticScope;
use PDO;

class DatabaseResolverForTests extends DatabaseResolver {

    public static function override(): void {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        StaticScope::set(parent::class, 'instance', $pdo);
    }

}
