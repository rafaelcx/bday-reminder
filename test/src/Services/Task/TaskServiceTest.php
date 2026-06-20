<?php

declare(strict_types=1);

namespace Test\Src\Services\Task;

use App\Repository\Task\Task;
use App\Repository\Task\TaskRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Messenger\Message;
use App\Services\Task\TaskService;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use Test\CustomTestCase;
use Test\Support\Services\Messenger\MessengerForTests;
use Test\Support\Services\Messenger\MessengerResolverForTests;

class TaskServiceTest extends CustomTestCase {

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2025-01-01 12:00:00');
    }

    public function testService_Notify_ShouldNotifyAllTasksFromAllUsers(): void {
        $user_1 = $this->createAndGetUser('user1');
        $user_2 = $this->createAndGetUser('user2');

        $this->createTaskForUser($user_1, 'task1');
        $this->createTaskForUser($user_1, 'task2');
        $this->createTaskForUser($user_2, 'task3');
        $this->createTaskForUser($user_2, 'task4');

        // Notifier behavior will be to concatenate all found user and task names into one string
        $execution_proof = '';
        $mock_notifier_behavior = function(User $user, string $message) use (&$execution_proof) {
            $execution_proof .= $user->name;
            $execution_proof .= $message;
        };

        // Registering the simulated Messenger
        $mock_notifier = new MessengerForTests();
        $mock_notifier->setPostBehavior($mock_notifier_behavior);
        MessengerResolverForTests::override($mock_notifier);

        TaskService::notify();

        $this->assertStringContainsString('user1', $execution_proof);
        $this->assertStringContainsString('user2', $execution_proof);
        $this->assertStringContainsString('task1', $execution_proof);
        $this->assertStringContainsString('task2', $execution_proof);
        $this->assertStringContainsString('task3', $execution_proof);
        $this->assertStringContainsString('task4', $execution_proof);
    }

    public function testService_Notify_ShouldIncludeTaskIdInMessage(): void {
        $user = $this->createAndGetUser('user1');

        $task_repo = TaskRepositoryResolver::resolve();
        $task_repo->create($user->uid, 'Important Task');

        $tasks = $task_repo->findByUserUid($user->uid);
        $task = array_pop($tasks);

        // Track the message received
        $received_message = '';
        $mock_notifier_behavior = function(User $_, string $message) use (&$received_message) {
            $received_message = $message;
        };

        // Registering the simulated Messenger
        $mock_notifier = new MessengerForTests();
        $mock_notifier->setPostBehavior($mock_notifier_behavior);
        MessengerResolverForTests::override($mock_notifier);

        TaskService::notify();

        // Verify task ID is in the message
        $this->assertInstanceOf(Task::class, $task);
        $this->assertStringContainsString($task->id, $received_message);
        $this->assertStringContainsString('Important Task', $received_message);
    }

    public function testService_Notify_ShouldIncludeTaskStatusInMessage(): void {
        $user = $this->createAndGetUser('user1');

        $task_repo = TaskRepositoryResolver::resolve();
        $task_repo->create($user->uid, 'Task To Complete');

        $tasks = $task_repo->findByUserUid($user->uid);
        $task = array_pop($tasks);

        // Track the message received
        $received_message = '';
        $mock_notifier_behavior = function(User $_, string $message) use (&$received_message) {
            $received_message = $message;
        };

        // Registering the simulated Messenger
        $mock_notifier = new MessengerForTests();
        $mock_notifier->setPostBehavior($mock_notifier_behavior);
        MessengerResolverForTests::override($mock_notifier);

        TaskService::notify();

        // Verify task status is in the message
        $this->assertInstanceOf(Task::class, $task);
        $this->assertStringContainsString($task->status->value, $received_message);
    }

    public function testService_Add_ShouldAddTasksFromAllUsers(): void {
        $user_1 = $this->createAndGetUser('user1');
        $user_2 = $this->createAndGetUser('user2');

        $update_1 = new Message(
            id: '1',
            message_id: '10',
            user_uid: $user_1->uid,
            text: 'task add "Task One"',
        );

        $update_2 = new Message(
            id: '2',
            message_id: '20',
            user_uid: $user_2->uid,
            text: 'task add "Task Two"',
        );

        $get_updates_behavior = fn() => [$update_1, $update_2];

        $mock_notifier = new MessengerForTests();
        $mock_notifier->setGetUpdatesBehavior($get_updates_behavior);
        MessengerResolverForTests::override($mock_notifier);

        TaskService::add();

        $user_1_tasks = TaskRepositoryResolver::resolve()->findByUserUid($user_1->uid);
        $user_2_tasks = TaskRepositoryResolver::resolve()->findByUserUid($user_2->uid);

        $this->assertCount(1, $user_1_tasks);
        $this->assertCount(1, $user_2_tasks);

        $user_1_task = array_pop($user_1_tasks);
        $user_2_task = array_pop($user_2_tasks);

        $this->assertInstanceOf(Task::class, $user_1_task);
        $this->assertInstanceOf(Task::class, $user_2_task);

        $this->assertSame('Task One', $user_1_task->title);
        $this->assertSame('Task Two', $user_2_task->title);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideSkipableUpdateText(): iterable {
        yield ['not_task add'];
        yield ['task not_add'];
        yield ['not_task not_add'];
        yield ['task'];
        yield ['add'];
    }

    #[DataProvider('provideSkipableUpdateText')]
    public function testService_Add_ShouldSkip(string $text): void {
        $user = $this->createAndGetUser('user1');

        $update_1 = new Message(
            id: '1',
            message_id: '10',
            user_uid: $user->uid,
            text: $text,
        );

        $get_updates_behavior = fn() => [$update_1];

        $mock_notifier = new MessengerForTests();
        $mock_notifier->setGetUpdatesBehavior($get_updates_behavior);
        MessengerResolverForTests::override($mock_notifier);

        TaskService::add();

        $user_tasks = TaskRepositoryResolver::resolve()->findByUserUid($user->uid);

        $this->assertCount(0, $user_tasks);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideMalformedUpdateTextParams(): iterable {
        yield ['task add'];
        yield ['task add ""'];
    }

    #[DataProvider('provideMalformedUpdateTextParams')]
    public function testService_Add_OnMalformedUpdateText_ShouldThrow(string $update_text): void {
        $user = $this->createAndGetUser('user1');

        $update_1 = new Message(
            id: '1',
            message_id: '10',
            user_uid: $user->uid,
            text: $update_text,
        );

        $get_updates_behavior = fn() => [$update_1];

        $mock_notifier = new MessengerForTests();
        $mock_notifier->setGetUpdatesBehavior($get_updates_behavior);
        MessengerResolverForTests::override($mock_notifier);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Task service `add` got unexpected params');

        TaskService::add();
    }

    private function createAndGetUser(string $user_name): User {
        $user_repo = UserRepositoryResolver::resolve();
        $user_repo->create($user_name);

        foreach ($user_repo->findAll() as $user) {
            if ($user->name === $user_name) {
                return $user;
            }
        }
        throw new \RuntimeException("User '$user_name' was not created.");
    }

    private function createTaskForUser(User $user, string $task_title): void {
        TaskRepositoryResolver::resolve()->create($user->uid, $task_title);
    }

}
