<?php

declare(strict_types=1);

namespace App\Repository\Credential;

use App\Storage\FileService;
use App\Storage\FileServiceResolver;
use App\Utils\Clock;

class CredentialRepositoryInFile implements CredentialRepository {

    private const FILE_NAME = 'credential-file.json';

    private FileService $file_service;

    public function __construct() {
        $this->file_service = FileServiceResolver::resolve();
        $this->ensureFileStructure();
    }

    public function create(string $id, string $data): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);

        $credential_list = $file_contents_as_obj->credentials;

        $new_credential = [
            'id' => $id,
            'data' => $data,
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ];
        $credential_list[] = $new_credential;

        $file_contents_as_obj->credentials = $credential_list;
        $updated_file_as_json = json_encode($file_contents_as_obj);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function findById(string $id): Credential {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        $persisted_credentials = $file_contents_as_obj->credentials;

        
        foreach ($persisted_credentials as $credential) {
            if ($credential->id === $id) {
                return $this->buildCredential($credential);
            }
        }
        throw new CredentialException('Credential not find for id: ' . $id);
    }

    private function buildCredential(\stdClass $credential_from_storage): Credential {
        return new Credential(
            id: $credential_from_storage->id,
            data: $credential_from_storage->data,
            created_at: Clock::at($credential_from_storage->created_at)
        );
    }

    private function ensureFileStructure(): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        if (!empty($file_contents)) {
            return;
        }
        $initial_file_state = json_encode(['credentials' => []]);
        $this->file_service->putFileContents(self::FILE_NAME, $initial_file_state);
    }

}
