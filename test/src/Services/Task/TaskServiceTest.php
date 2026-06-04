<?php

declare(strict_types=1);

namespace Test\Src\Services\Task;

use App\Repository\Task\Task;
use App\Repository\Task\TaskRepositoryResolver;
use App\Repository\User\User;
use App\Repository\User\UserRepositoryResolver;
use App\Services\Task\TaskService;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
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
