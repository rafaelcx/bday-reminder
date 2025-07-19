<?php

declare(strict_types=1);

namespace Test\Support\Repository\Credential;

use App\Repository\Credential\Credential;
use App\Repository\Credential\CredentialRepository;

class CredentialRepositoryForTests implements CredentialRepository {

    private array $mock_data;

    public function __construct() {
        $this->mock_data = [];
    }

    public function create(string $id, string $data): void {
        // No-op
    }

    public function findById(string $id): Credential {
        if (!isset($this->mock_data[$id])) {
            throw new \LogicException('You should set mock data for credential fetch logic');
        }
        return $this->mock_data[$id];
    }

    public function mockCredentialData(string $id, Credential $credential): void {
        $this->mock_data[$id] = $credential;
    }

}
