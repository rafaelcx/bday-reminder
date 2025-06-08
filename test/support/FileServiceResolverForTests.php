<?php

declare(strict_types=1);

namespace Test\Support;

use App\Storage\FileServiceDefault;
use App\Storage\FileServiceResolver;

class FileServiceResolverForTests extends FileServiceResolver {

    public static function override(): void {
        self::$instance = new FileServiceDefault(__DIR__ . '/');
    }

    public static function reset(): void {
        $files = glob(__DIR__ . '/*.json');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

}
