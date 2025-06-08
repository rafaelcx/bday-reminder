<?php

declare(strict_types=1);

namespace App\Storage;

class FileServiceResolver {

    protected static ?FileService $instance = null;

    public static function resolve(): FileService {
        if (is_null(self::$instance)) {
            self::createInstance();
        }
        return self::$instance;
    }

    private static function createInstance(): void {
        $file_location = __DIR__ . '/Files/'; 
        self::$instance = new FileServiceDefault($file_location);
    }

}
