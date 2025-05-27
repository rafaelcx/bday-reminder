<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

class BirthdayRepositoryResolver {

    protected static ?BirthdayRepository $instance = null;

    public static function resolve(): BirthdayRepository {
        if (is_null(self::$instance)) {
            self::createInstance();
        }
        return self::$instance;
    }

    private static function createInstance(): void {
        self::$instance = new BirthdayRepositoryInFile('/birthday-file.json');
    }

}
