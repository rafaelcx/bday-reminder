<?php

declare(strict_types=1);

namespace App\Repository\User;

use App\Storage\FileService;
use App\Storage\FileServiceResolver;
use App\Utils\Clock;

class UserRepositoryInFile implements UserRepository {

    private const FILE_NAME = 'user-file.json';

    private FileService $file_service;

    public function __construct() {
        $this->file_service = FileServiceResolver::resolve();
        $this->ensureFileStructure();
    }

    public function create(string $name): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        
        $user_list = $file_contents_as_obj->users;
        
        $new_user = [
            'uid' => uniqid(),
            'name' => $name,
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ];
        $user_list[] = $new_user;

        $file_contents_as_obj->users = $user_list;
        $updated_file_as_json = json_encode($file_contents_as_obj);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function findAll(): array {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        $persisted_users = $file_contents_as_obj->users;

        $fn = function(\stdClass $user) {
            return new User($user->uid, $user->name, Clock::at($user->created_at));
        };
        return array_map($fn, $persisted_users);
    }

    private function ensureFileStructure(): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        if (!empty($file_contents)) {
            return;
        }
        $initial_file_state = json_encode(['users' => []]);
        $this->file_service->putFileContents(self::FILE_NAME, $initial_file_state);
    }

}
