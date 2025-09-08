<?php

declare(strict_types=1);

namespace App\Storage;

use App\Utils\StaticScope;

class FileServiceResolver {

    public static function resolve(): FileService {
        return StaticScope::getOrCreate(self::class, 'instance', self::createInstance(...));
    }

    private static function createInstance(): FileService {
        $files_location = __DIR__ . '/Files/';
        return new FileServiceDefault($files_location);
    }

}
