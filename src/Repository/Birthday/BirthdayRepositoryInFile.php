<?php

declare(strict_types=1);

namespace App\Repository\Birthday;

class BirthdayRepositoryInFile implements BirthdayRepository {

    public string $file_name;

    public function __construct(string $file_name) {
        $this->file_name = __DIR__ . $file_name;
        $this->ensureFileExists();
    }

    public function create(string $user_uid, string $name, \DateTime $date): void {
        $file_contents = file_get_contents($this->file_name);
        $file_contents_as_obj = json_decode($file_contents);
        
        $birthday_list = $file_contents_as_obj->birthdays;
        
        $new_birthday = [
            'uid' => uniqid(),
            'user_uid' => $user_uid,
            'name' => $name,
            'date' => $date->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        $birthday_list[] = $new_birthday;

        $file_contents_as_obj->birthdays = $birthday_list;
        $updated_file_as_json = json_encode($file_contents_as_obj);
        file_put_contents($this->file_name, $updated_file_as_json);
    }

    public function findByUserUid(string $user_uid): array {
        $file_contents = file_get_contents($this->file_name);
        $file_contents_as_obj = json_decode($file_contents);
        $all_persisted_birthdays = $file_contents_as_obj->birthdays;

        $fn = function (\stdClass $birthday) use ($user_uid) {
            return $birthday->user_uid === $user_uid;
        };
        $filtered_birthdays = array_filter($all_persisted_birthdays, $fn);

        $fn = function(\stdClass $birthday) {
            return new Birthday(
                $birthday->uid, 
                $birthday->user_uid, 
                $birthday->name, 
                new \DateTime($birthday->date), 
                new \DateTime($birthday->created_at)
            );
        };
        return array_map($fn, $filtered_birthdays);
    }

    private function ensureFileExists(): void {
        if (file_exists($this->file_name)) {
            return;
        }
        $initial_file_state = ['birthdays' => []];
        $initial_file_state = json_encode($initial_file_state);
        file_put_contents($this->file_name, $initial_file_state);
    }

}
