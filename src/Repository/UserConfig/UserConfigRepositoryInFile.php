<?php

declare(strict_types=1);

namespace App\Repository\UserConfig;

use App\Storage\FileService;
use App\Storage\FileServiceResolver;

class UserConfigRepositoryInFile implements UserConfigRepository {

    private const FILE_NAME = 'config-file.json';

    private FileService $file_service;

    public function __construct() {
        $this->file_service = FileServiceResolver::resolve();
        $this->ensureFileStructure();
    }

    public function create(string $user_uid, string $name, string $value): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);

        $config_list = $file_contents_as_obj->user_configs;

        $new_config = [
            'uid' => uniqid(),
            'user_uid' => $user_uid,
            'name' => $name,
            'value' => $value,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        $config_list[] = $new_config;

        $file_contents_as_obj->user_configs = $config_list;
        $updated_file_as_json = json_encode($file_contents_as_obj);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function findByUserUidAndName(string $user_uid, string $name): UserConfig {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        $persisted_configs = $file_contents_as_obj->user_configs;

        foreach ($persisted_configs as $config) {
            if ($config->user_uid === $user_uid && $config->name === $name) {
                return $this->buildConfig($config);
            }
        }
        $error_msg = "Config not found for user with uid `{$user_uid}` and name `{$name}`";
        throw new UserConfigException($error_msg);
    }

    private function buildConfig(\stdClass $config): UserConfig {
        return new UserConfig(
            uid: $config->uid,
            user_uid: $config->user_uid,
            name: $config->name,
            value: $config->value,
            created_at: new \DateTime($config->created_at),
            updated_at: new \DateTime($config->updated_at),
        );
    }

    private function ensureFileStructure(): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        if (!empty($file_contents)) {
            return;
        }
        $initial_file_state = json_encode(['user_configs' => []]);
        $this->file_service->putFileContents(self::FILE_NAME, $initial_file_state);
    }

}
