<?php

declare(strict_types=1);

namespace Test\Support\Files\Service;

use App\Storage\Files\Service\FileServiceDefault;
use App\Storage\Files\Service\FileServiceResolver;
use App\Utils\StaticScope;

class FileServiceResolverForTests extends FileServiceResolver {

    public static function override(): void {
        $service = new FileServiceDefault(__DIR__ . '/');
        StaticScope::set(parent::class, 'instance', $service);
    }

    public static function reset(): void {
        $files = glob(__DIR__ . '/*.json');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

}
