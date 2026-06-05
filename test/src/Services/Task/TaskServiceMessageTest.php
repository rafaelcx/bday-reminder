<?php

declare(strict_types=1);

namespace Test\Src\Services\Task;

use App\Repository\Task\Task;
use App\Repository\Task\TaskStatus;
use App\Repository\User\User;
use App\Services\Task\TaskServiceMessage;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\CustomTestCase;

class TaskServiceMessageTest extends CustomTestCase {

    private User $test_user;

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2025-07-20 12:00:00');
    }

    #[Before]
    protected function setUpUserForTests(): void {
        $this->test_user = new User(
            uid: 'user-123',
            name: 'Alice',
            created_at: Clock::now()
        );
    }

    public function testBuilder_ShouldReturnNoTaskMessageWhenEmpty(): void {
        $message = TaskServiceMessage::build($this->test_user, ...[]);
        $expected_message = <<<TXT
        Hello Alice,

        ✅ You have no pending tasks!

        🎉 Great job! You're all caught up!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldFormatSingleTaskWithPendingStatus(): void {
        $task = new Task(
            id: 'task-001',
            user_uid: $this->test_user->uid,
            title: 'Complete Project',
            status: TaskStatus::DOING,
            created_at: Clock::now(),
            updated_at: Clock::now()
        );

        $message = TaskServiceMessage::build($this->test_user, $task);
        $expected_message = <<<TXT
        Hello Alice,

        Here are your tasks:

        📋 [task-001] Complete Project
           Status: DOING

        💪 Keep up the great work!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldFormatTaskWithCompletedStatus(): void {
        $task = new Task(
            id: 'task-002',
            user_uid: $this->test_user->uid,
            title: 'Finished Task',
            status: TaskStatus::DONE,
            created_at: Clock::now(),
            updated_at: Clock::now()
        );

        $message = TaskServiceMessage::build($this->test_user, $task);
        $expected_message = <<<TXT
        Hello Alice,

        Here are your tasks:

        ✅ [task-002] Finished Task
           Status: DONE

        💪 Keep up the great work!
        TXT;
        $this->assertSame($expected_message, $message);
    }

    public function testBuilder_ShouldSortPendingTasksBeforeCompletedTasks(): void {
        $pending_task = new Task(
            id: 'task-001',
            user_uid: $this->test_user->uid,
            title: 'Pending Task',
            status: TaskStatus::DOING,
            created_at: Clock::now()->minusDays(1),
            updated_at: Clock::now()->minusDays(1)
        );

        $completed_task = new Task(
            id: 'task-002',
            user_uid: $this->test_user->uid,
            title: 'Completed Task',
            status: TaskStatus::DONE,
            created_at: Clock::now()->minusDays(2),
            updated_at: Clock::now()->minusDays(2)
        );

        $message = TaskServiceMessage::build($this->test_user, $completed_task, $pending_task);

        $pending_pos = strpos($message, 'Pending Task');
        $completed_pos = strpos($message, 'Completed Task');

        $this->assertLessThan($pending_pos, $completed_pos, 'DONE tasks should appear before DOING tasks');
    }

    public function testBuilder_ShouldFormatMultipleTasks(): void {
        $task_1 = new Task(
            id: 'task-001',
            user_uid: $this->test_user->uid,
            title: 'First Task',
            status: TaskStatus::DOING,
            created_at: Clock::now()->minusDays(2),
            updated_at: Clock::now()->minusDays(2)
        );

        $task_2 = new Task(
            id: 'task-002',
            user_uid: $this->test_user->uid,
            title: 'Second Task',
            status: TaskStatus::DOING,
            created_at: Clock::now()->minusDays(1),
            updated_at: Clock::now()->minusDays(1)
        );

        $message = TaskServiceMessage::build($this->test_user, $task_1, $task_2);

        $this->assertStringContainsString('task-001', $message);
        $this->assertStringContainsString('First Task', $message);
        $this->assertStringContainsString(TaskStatus::DOING->value, $message);

        $this->assertStringContainsString('task-002', $message);
        $this->assertStringContainsString('Second Task', $message);
        $this->assertStringContainsString(TaskStatus::DOING->value, $message);
    }

}
