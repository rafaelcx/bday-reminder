<?php

declare(strict_types=1);

namespace Test\Support;

use App\Storage\FileServiceDefault;
use App\Storage\FileServiceResolver;
use App\Utils\StaticScope;

class FileServiceResolverForTests extends FileServiceResolver {

    public static function override(): void {
        $service = new FileServiceDefault(__DIR__ . '/');
        StaticScope::set(parent::class, 'instance', $service);
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
