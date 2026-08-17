<?php

declare(strict_types=1);

namespace App\Storage\Database\Seed;

use App\Storage\Database\DatabaseAction;

class SeedRegistry {

    /**
     * @return DatabaseAction[]
     */
    public function getRegisteredSeeds(): array {
        return [
            new \App\Storage\Database\Seed\Files\S20260817000000_SeedStorageData(),
        ];
    }

}
