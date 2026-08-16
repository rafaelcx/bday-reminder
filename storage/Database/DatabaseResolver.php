<?php

declare(strict_types=1);

namespace App\Storage\Database;

use App\Utils\StaticScope;
use PDO;

class DatabaseResolver {

    public static function resolve(): PDO {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): PDO {
        $db_path = __DIR__ . '/../../db/database.sqlite';
        $pdo = new PDO('sqlite:' . $db_path);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

}
