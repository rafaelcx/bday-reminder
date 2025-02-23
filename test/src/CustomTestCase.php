<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Slim\App;

class CustomTestCase extends TestCase {

    public function getAppInstance(): App {
        return require __DIR__ . '/../../app/app.php';
    }

}
