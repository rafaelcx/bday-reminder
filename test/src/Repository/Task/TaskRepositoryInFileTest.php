<?php

declare(strict_types=1);

namespace Test\Src\Repository\Task;

use App\Repository\Task\TaskRepositoryInFile;
use App\Repository\Task\TaskStatus;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\CustomTestCase;

class TaskRepositoryInFileTest extends CustomTestCase {

    private TaskRepositoryInFile $task_repository;

    #[Before]
    public function prepareTaskRepositoryForTests(): void {
        $this->task_repository = new TaskRepositoryInFile();
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

        $persisted_tasks_user_2 = $this->task_repository->findByUserUid('user_uid_2');
        $this->assertCount(1, $persisted_tasks_user_2);
        $this->assertSame('3', $persisted_tasks_user_2[0]->id);
        $this->assertSame('user_uid_2', $persisted_tasks_user_2[0]->user_uid);
        $this->assertSame('Buy groceries', $persisted_tasks_user_2[0]->title);
    }

    public function testRepository_CreateAssignsSequentialIds_AfterDeletion(): void {
        $this->task_repository->create('user_uid_1', 'Task one');
        $this->task_repository->create('user_uid_1', 'Task two');

        $this->task_repository->delete('1');
        $this->task_repository->create('user_uid_1', 'Task three');

        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $this->assertCount(2, $persisted_tasks);
        $this->assertSame('2', $persisted_tasks[0]->id);
        $this->assertSame('3', $persisted_tasks[1]->id);
        $this->assertSame('Task two', $persisted_tasks[0]->title);
        $this->assertSame('Task three', $persisted_tasks[1]->title);
    }

    public function testRepository_FindByUserUid_OnFreshFile(): void {
        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $this->assertSame([], $persisted_tasks);
    }

    public function testRepository_CompleteTask(): void {
        $this->task_repository->create('user_uid_1', 'Buy milk');
        $this->task_repository->create('user_uid_1', 'Call dentist');

        $all_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $first_task = $all_tasks[0];
        
        $this->assertSame(TaskStatus::DOING, $first_task->status);

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
        $this->assertNotEmpty($persisted_tasks[0]->id);
        $this->assertSame('user_uid_1', $persisted_tasks[0]->user_uid);
        $this->assertSame('Call dentist', $persisted_tasks[0]->title);
        $this->assertSame(TaskStatus::DOING, $persisted_tasks[0]->status);
    }

    public function testRepository_FindByUserUidAfterDate(): void {
        Clock::freeze('2026-05-01 12:00:00');
        $this->task_repository->create('user_uid_1', 'Task from May');

        Clock::freeze('2026-05-15 12:00:00');
        $this->task_repository->create('user_uid_1', 'Task from mid May');

        Clock::freeze('2026-06-01 12:00:00');
        $this->task_repository->create('user_uid_1', 'Task from June');
        $this->task_repository->create('user_uid_1', 'Another task from June');

        $tasks_after_may_10 = $this->task_repository->findByUserUidAfterDate(
            'user_uid_1',
            Clock::at('2026-05-10')
        );

        // Should only include tasks created after 2026-05-10
        $this->assertCount(3, $tasks_after_may_10);
        $task_titles = array_map(fn($t) => $t->title, $tasks_after_may_10);
        $this->assertContains('Task from mid May', $task_titles);
        $this->assertContains('Task from June', $task_titles);
        $this->assertContains('Another task from June', $task_titles);
        $this->assertNotContains('Task from May', $task_titles);
    }

    public function testRepository_FindByUserUidAfterDate_ShouldNotIncludeTasks_CreatedOnOrBeforeFilter(): void {
        Clock::freeze('2026-05-10 12:00:00');
        $this->task_repository->create('user_uid_1', 'Task on filter date');

        Clock::freeze('2026-05-11 12:00:00');
        $this->task_repository->create('user_uid_1', 'Task after filter date');

        $tasks_after = $this->task_repository->findByUserUidAfterDate(
            'user_uid_1',
            Clock::at('2026-05-10')
        );

        // Should only include tasks created strictly after the filter date
        $this->assertCount(1, $tasks_after);
        $this->assertSame('Task after filter date', $tasks_after[0]->title);
    }

    public function testRepository_FindByUserUidAfterDate_WithMultipleUsers(): void {
        Clock::freeze('2026-05-01 12:00:00');
        $this->task_repository->create('user_uid_1', 'User 1 task from May');
        $this->task_repository->create('user_uid_2', 'User 2 task from May');

        Clock::freeze('2026-06-01 12:00:00');
        $this->task_repository->create('user_uid_1', 'User 1 task from June');
        $this->task_repository->create('user_uid_2', 'User 2 task from June');

        $user_1_tasks = $this->task_repository->findByUserUidAfterDate(
            'user_uid_1',
            Clock::at('2026-05-15')
        );

        // Should only include user_uid_1 tasks created after 2026-05-15
        $this->assertCount(1, $user_1_tasks);
        $this->assertSame('User 1 task from June', $user_1_tasks[0]->title);
        $this->assertSame('user_uid_1', $user_1_tasks[0]->user_uid);
    }

    public function testRepository_StatusTransition_FromDoingToDone(): void {
        $this->task_repository->create('user_uid_1', 'Complete me');
        $all_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $task_id = $all_tasks[0]->id;

        Clock::freeze('2026-06-10 12:00:00');
        $this->task_repository->completeTask($task_id);

        $persisted_tasks = $this->task_repository->findByUserUid('user_uid_1');
        $this->assertSame(TaskStatus::DONE, $persisted_tasks[0]->status);
        $this->assertSame('2026-06-04', $persisted_tasks[0]->created_at->asDateString());
        $this->assertSame('2026-06-10', $persisted_tasks[0]->updated_at->asDateString());
    }

}
