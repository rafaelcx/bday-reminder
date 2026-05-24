<?php

declare(strict_types=1);

namespace App\Utils;

class JsonEncoder {

    public static function safeEncode(mixed $value, int $options = 0): string {
        $encoded = json_encode($value, $options);
        if ($encoded === false) {
            throw new \RuntimeException('Unable to encode user config repository JSON.');
        }
        return $encoded;
    }

}
