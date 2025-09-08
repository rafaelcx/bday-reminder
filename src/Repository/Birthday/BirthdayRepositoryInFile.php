<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

use App\Storage\FileService;
use App\Storage\FileServiceResolver;
use App\Utils\Clock;

class BirthdayRepositoryInFile implements BirthdayRepository {

    private const FILE_NAME = 'birthday-file.json';
    
    private FileService $file_service;

    public function __construct() {
        $this->file_service = FileServiceResolver::resolve();
        $this->ensureFileSchema();
    }

    public function create(string $user_uid, string $name, Clock $date): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        
        $birthday_list = $file_contents_as_obj->birthdays;
        
        $new_birthday = [
            'uid' => uniqid(),
            'user_uid' => $user_uid,
            'name' => $name,
            'date' => $date->format('Y-m-d H:i:s'),
            'created_at' => Clock::now()->format('Y-m-d H:i:s'),
        ];
        $birthday_list[] = $new_birthday;

        $file_contents_as_obj->birthdays = $birthday_list;
        $updated_file_as_json = json_encode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function findByUserUid(string $user_uid): array {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);

        $birthday_list = $file_contents_as_obj->birthdays;

        $fn = fn(\stdClass $birthday) => $birthday->user_uid === $user_uid;
        $filtered_birthdays = array_filter($birthday_list, $fn);

        $birthday_list = [];
        foreach ($filtered_birthdays as $birthday) {
            $birthday_list[] = new Birthday($birthday->uid,
                $birthday->user_uid,
                $birthday->name,
                Clock::at($birthday->date),
                Clock::at($birthday->created_at)
            );
        }
        return $birthday_list;
    }

    public function update(string $birthday_uid, string $name, Clock $date): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);

        $file_contents_as_obj = json_decode($file_contents);
        $all_persisted_birthdays = $file_contents_as_obj->birthdays;

        foreach ($all_persisted_birthdays as $index => $persisted_birthday) {
            if ($persisted_birthday->uid === $birthday_uid) {
                $all_persisted_birthdays[$index]->name = $name;
                $all_persisted_birthdays[$index]->date = $date->format('Y-m-d H:i:s');
            }
        }

        $file_contents_as_obj->birthdays = $all_persisted_birthdays;
        $updated_file_as_json = json_encode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function delete(string $birthday_uid): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);

        $file_contents_as_obj = json_decode($file_contents);
        $all_persisted_birthdays = $file_contents_as_obj->birthdays;

        foreach ($all_persisted_birthdays as $index => $persisted_birthday) {
            if ($persisted_birthday->uid === $birthday_uid) {
                unset($all_persisted_birthdays[$index]);
            }
        }

        // Reindex the array to avoid strange numeric keys in the updated JSON
        $file_contents_as_obj->birthdays = array_values($all_persisted_birthdays);
        
        $updated_file_as_json = json_encode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    private function ensureFileSchema(): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        if (!empty($file_contents)) {
            return;
        }
        $initial_file_state = json_encode(['birthdays' => []]);
        $this->file_service->putFileContents(self::FILE_NAME, $initial_file_state);
    }

}
