<?php

declare(strict_types=1);

namespace App\Repository\User;

class UserRepositoryInFile implements UserRepository {

    public string $file_name;

    public function __construct(string $file_name) {
        $this->file_name = __DIR__ . $file_name;
        $this->ensureFileExists();
    }

    public function create(string $name): void {
        $file_contents = file_get_contents($this->file_name);
        $file_contents_as_obj = json_decode($file_contents);
        
        $user_list = $file_contents_as_obj->users;
        
        $new_user = [
            'uid' => uniqid(),
            'name' => $name,
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];
        $user_list[] = $new_user;

        $file_contents_as_obj->users = $user_list;
        $updated_file_as_json = json_encode($file_contents_as_obj);
        file_put_contents($this->file_name, $updated_file_as_json);
    }

    public function findAll(): array {
        $file_contents = file_get_contents($this->file_name);
        $file_contents_as_obj = json_decode($file_contents);
        $persisted_users = $file_contents_as_obj->users;

        $fn = function(\stdClass $user) {
            return new User($user->uid, $user->name, new \DateTime($user->created_at));
        };
        return array_map($fn, $persisted_users);
    }

    private function ensureFileExists(): void {
        if (file_exists($this->file_name)) {
            return;
        }
        $initial_file_state = ['users' => []];
        $initial_file_state = json_encode($initial_file_state);
        file_put_contents($this->file_name, $initial_file_state);
    }

}
