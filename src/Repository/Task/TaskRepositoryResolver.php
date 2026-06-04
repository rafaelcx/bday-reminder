<?php

declare(strict_types=1);

namespace App\Repository\Task;

use App\Utils\StaticScope;

class TaskRepositoryResolver {

    public static function resolve(): TaskRepository {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): TaskRepository {
        return new TaskRepositoryInFile();
    }

}
