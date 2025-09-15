<?php

declare(strict_types=1);

namespace App\Logger;

class LoggerService {

    public static function cleanLogs(): void {
        $logger = LoggerResolver::resolve();

        if ($logger instanceof LoggerDefault) {
            $logger->cleanLogFile();
        }
    }

}
