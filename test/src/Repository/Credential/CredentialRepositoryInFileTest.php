<?php

declare(strict_types=1);

namespace Test\Src\Repository\Credential;

use App\Repository\Credential\CredentialException;
use App\Repository\Credential\CredentialRepositoryInFile;
use App\Utils\Clock;
use Test\CustomTestCase;

class CredentialRepositoryInFileTest extends CustomTestCase {

    /**
     * @before
     */
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testRepository(): void {
        $repository = new CredentialRepositoryInFile();

        $repository->create('id_1', 'data_1');
        $repository->create('id_2', 'data_2');

        $credential_1 = $repository->findById('id_1');
        $credential_2 = $repository->findById('id_2');

        $this->assertSame('id_1', $credential_1->id);
        $this->assertSame('data_1', $credential_1->data);
        $this->assertSame('2025-01-01 12:00:00', $credential_1->created_at->asDateTimeString());

        $this->assertSame('id_2', $credential_2->id);
        $this->assertSame('data_2', $credential_2->data);
        $this->assertNotNull('2025-01-01 12:00:00', $credential_2->created_at->asDateTimeString());
    }

    public function testRepository_FindById_WhenCredentialsDoesNotExist(): void {
        $repository = new CredentialRepositoryInFile();
        $this->expectException(CredentialException::class);
        $repository->findById('not-existent');
    }

}
