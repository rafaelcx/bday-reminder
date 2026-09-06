<?php

declare(strict_types=1);

namespace Test\Src\Repository\Task;

use App\Repository\Task\Task;
use App\Repository\Task\TaskRepositoryInSqlite;
use App\Repository\Task\TaskStatus;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\DbCustomTestCase;

class TaskRepositoryInSqliteTest extends DbCustomTestCase {

    private TaskRepositoryInSqlite $task_repository;

    #[Before]
    public function prepareTaskRepositoryForTests(): void {
        $this->task_repository = new TaskRepositoryInSqlite();
    }

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2026-06-04 12:00:00');
    }

    public function testRepository_CreateAndFindByUserUid(): void {
        $this->task_repository->create('user_uid_1', 'Buy milk');
        $this->task_repository->create('user_uid_1', 'Call dentist');
        $this->task_repository->create('user_uid_2', 'Buy groceries');

        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');

        $this->assertCount(2, $persisted_tasks);
        $this->assertSame('1', $persisted_tasks[0]->id);
        $this->assertSame('user_uid_1', $persisted_tasks[0]->user_uid);
        $this->assertSame('Buy milk', $persisted_tasks[0]->title);
        $this->assertSame(TaskStatus::DOING, $persisted_tasks[0]->status);
        $this->assertSame('2026-06-04', $persisted_tasks[0]->created_at->asDateString());
        $this->assertSame('2026-06-04', $persisted_tasks[0]->updated_at->asDateString());

        $this->assertSame('2', $persisted_tasks[1]->id);
        $this->assertSame('user_uid_1', $persisted_tasks[1]->user_uid);
        $this->assertSame('Call dentist', $persisted_tasks[1]->title);
        $this->assertSame(TaskStatus::DOING, $persisted_tasks[1]->status);
        $this->assertSame('2026-06-04', $persisted_tasks[1]->created_at->asDateString());
        $this->assertSame('2026-06-04', $persisted_tasks[1]->updated_at->asDateString());
    }

    public function testRepository_CompleteTask(): void {
        $this->task_repository->create('user_uid_1', 'Buy milk');
        $this->task_repository->create('user_uid_1', 'Call dentist');

        $all_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $first_task = $all_tasks[0];

        $this->task_repository->completeTask($first_task->id);

        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $this->assertCount(2, $persisted_tasks);
        $this->assertSame(TaskStatus::DONE, $persisted_tasks[0]->status);
        $this->assertSame('2026-06-04', $persisted_tasks[0]->updated_at->asDateString());
        $this->assertSame(TaskStatus::DOING, $persisted_tasks[1]->status);
    }

    public function testRepository_Delete(): void {
        $this->task_repository->create('user_uid_1', 'Buy milk');
        $this->task_repository->create('user_uid_1', 'Call dentist');

        $all_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $first_task = $all_tasks[0];

        $this->task_repository->delete($first_task->id);
        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');

        $this->assertCount(1, $persisted_tasks);
        $this->assertSame('Call dentist', $persisted_tasks[0]->title);
        $this->assertSame(TaskStatus::DOING, $persisted_tasks[0]->status);
    }

    public function testRepository_FindByUserUidAfterDate(): void {
        Clock::freeze('2026-05-01 12:00:00');
        $task_1 = $this->createAndReturnTask('user_uid_1', 'Task One');

        Clock::freeze('2026-05-01 12:00:00');
        $task_2 = $this->createAndReturnTask('user_uid_1', 'Task Two');
        $this->task_repository->completeTask($task_2->id);

        Clock::freeze('2026-05-15 12:00:00');
        $task_3 = $this->createAndReturnTask('user_uid_1', 'Task Three');

        Clock::freeze('2026-05-15 12:00:00');
        $task_4 = $this->createAndReturnTask('user_uid_1', 'Task Four');
        $this->task_repository->completeTask($task_4->id);

        Clock::freeze('2026-05-16 12:00:00');
        $task_5 = $this->createAndReturnTask('user_uid_1', 'Task Five');
        $this->task_repository->completeTask($task_5->id);

        Clock::freeze('2026-05-16 12:00:00');
        $task_6 = $this->createAndReturnTask('user_uid_2', 'Task Six');

        $found_tasks = $this->task_repository->findByUserUidAfterDate('user_uid_1', Clock::at('2026-05-10'));

        $this->assertCount(4, $found_tasks);
        $this->assertSame($task_1->id, $found_tasks[0]->id);
        $this->assertSame($task_3->id, $found_tasks[1]->id);
        $this->assertSame($task_4->id, $found_tasks[2]->id);
        $this->assertSame($task_5->id, $found_tasks[3]->id);
    }

    private function createAndReturnTask(string $user_uid, string $title): Task {
        $this->task_repository->create($user_uid, $title);
        $tasks = $this->task_repository->findByUserUid($user_uid);
        foreach ($tasks as $task) {
            if ($task->title === $title) {
                return $task;
            }
        }
        throw new \Exception('Could not create or find task to return');
    }

}
