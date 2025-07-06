<?php

declare(strict_types=1);

namespace App\Repository\Credential;

interface CredentialRepository {

    public function create(string $id, string $data);
    public function findById(string $id): Credential;

}
