<?php

declare(strict_types=1);

namespace App\Repository\Task;

use App\Storage\FileService;
use App\Storage\FileServiceResolver;
use App\Utils\Clock;
use App\Utils\JsonEncoder;

class TaskRepositoryInFile implements TaskRepository {

    private const string FILE_NAME = 'task-file.json';
    private const string STATUS_DOING = 'DOING';
    private const string STATUS_DONE = 'DONE';
    
    private FileService $file_service;

    public function __construct() {
        $this->file_service = FileServiceResolver::resolve();
        $this->ensureFileSchema();
    }

    public function create(string $user_uid, string $title): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        
        $task_list = $file_contents_as_obj->tasks;
        
        $now = Clock::now()->format('Y-m-d');
        $new_task = [
            'id' => $this->nextId($task_list),
            'user_uid' => $user_uid,
            'title' => $title,
            'status' => self::STATUS_DOING,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $task_list[] = $new_task;

        $file_contents_as_obj->tasks = $task_list;
        $updated_file_as_json = JsonEncoder::safeEncode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function findByUserUid(string $user_uid): array {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);

        $task_list = $file_contents_as_obj->tasks;

        $fn = fn(\stdClass $task) => $task->user_uid === $user_uid;
        $filtered_tasks = array_filter($task_list, $fn);

        $task_list = [];
        foreach ($filtered_tasks as $task) {
            $task_list[] = new Task(
                $task->id,
                $task->user_uid,
                $task->title,
                $task->status,
                Clock::at($task->created_at),
                Clock::at($task->updated_at)
            );
        }
        return $task_list;
    }

    public function findByUserUidAfterDate(string $user_uid, Clock $date): array {
        $all_tasks = $this->findByUserUid($user_uid);
        
        $relevant_tasks = array_filter($all_tasks, function(Task $t) use ($date) {
            $task_created_date = $t->created_at->format('Y-m-d');
            $filter_date = $date->format('Y-m-d');
            return $task_created_date > $filter_date;
        });

        return array_values($relevant_tasks);
    }

    public function completeTask(string $task_id): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        $file_contents_as_obj = json_decode($file_contents);
        $all_persisted_tasks = $file_contents_as_obj->tasks;

        foreach ($all_persisted_tasks as $index => $persisted_task) {
            if ($persisted_task->id === $task_id) {
                $all_persisted_tasks[$index]->status = self::STATUS_DONE;
                $all_persisted_tasks[$index]->updated_at = Clock::now()->format('Y-m-d');
            }
        }

        $file_contents_as_obj->tasks = $all_persisted_tasks;
        $updated_file_as_json = JsonEncoder::safeEncode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    public function delete(string $task_id): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);

        $file_contents_as_obj = json_decode($file_contents);
        $all_persisted_tasks = $file_contents_as_obj->tasks;

        foreach ($all_persisted_tasks as $index => $persisted_task) {
            if ($persisted_task->id === $task_id) {
                unset($all_persisted_tasks[$index]);
            }
        }

        // Reindex the array to avoid strange numeric keys in the updated JSON
        $file_contents_as_obj->tasks = array_values($all_persisted_tasks);
        
        $updated_file_as_json = JsonEncoder::safeEncode($file_contents_as_obj, JSON_PRETTY_PRINT);
        $this->file_service->putFileContents(self::FILE_NAME, $updated_file_as_json);
    }

    /**
     * @param Task[] $tasks
     */
     private function nextId(array $tasks): string {
        if (empty($tasks)) {
            return '1';
        }

        $max_id = 0;
        foreach ($tasks as $task) {
            if (ctype_digit($task->id)) {
                $max_id = max($max_id, (int) $task->id);
            }
        }

        return (string) ($max_id + 1);
    }

    private function ensureFileSchema(): void {
        $file_contents = $this->file_service->getFileContents(self::FILE_NAME);
        if (!empty($file_contents)) {
            return;
        }
        $initial_file_state = JsonEncoder::safeEncode(['tasks' => []]);
        $this->file_service->putFileContents(self::FILE_NAME, $initial_file_state);
    }

}
