<?php

declare(strict_types=1);

namespace App\Storage\Database;

use App\Utils\Environment;
use App\Utils\EnvironmentType;
use App\Utils\StaticScope;
use PDO;
use RuntimeException;

class DatabaseResolver {

    public static function resolve(): PDO {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): PDO {
        self::validateEnvironment();

        $db_path = __DIR__ . '/../../db/database.sqlite';
        $pdo = new PDO('sqlite:' . $db_path);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    private static function validateEnvironment(): void {
        if (Environment::getCurrent() === EnvironmentType::TEST) {
            $err_msg = <<<MSG
                Forbidden usage of Sqlite file. Only in-memory implementations are allowed on test environments.
                Make sure this test class extends `DbCustomTestCase::class`.
                MSG;
            throw new RuntimeException($err_msg);
        }
    }

}
