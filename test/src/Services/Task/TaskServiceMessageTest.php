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
        Here are you tasks {$this->test_user->name}.

        The first ones are pending while the things you done in the last two weeks shows on the botton.

        ✅ You have no pending tasks though, congrats!
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
        Here are you tasks {$this->test_user->name}.

        The first ones are pending while the things you done in the last two weeks shows on the botton.

        📋 [task-001] Complete Project

        TXT;
        $this->assertSame($expected_message, $message);
    }


    public function testBuilder_ShouldSortDoneTasksBeforePendingTasks(): void {
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

        $expected_message = <<<TXT
        Here are you tasks {$this->test_user->name}.

        The first ones are pending while the things you done in the last two weeks shows on the botton.

        📋 [task-001] Pending Task

        ✅ [task-002] Completed Task
        TXT;
        $this->assertSame($expected_message, $message);
    }

}
