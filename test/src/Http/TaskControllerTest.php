<?php

declare(strict_types=1);

namespace Test\Src\Http;

use App\Repository\Task\TaskRepositoryResolver;
use App\Utils\Clock;
use PHPUnit\Framework\Attributes\Before;
use Test\CustomTestCase;

class TaskControllerTest extends CustomTestCase {

    #[Before]
    public function freezeClockForTests(): void {
        Clock::freeze('2026-06-04 12:00:00');
    }

    public function testController_ShowsTasksForUser(): void {
        $task_repository = TaskRepositoryResolver::resolve();
        $task_repository->create('user_1', 'Buy groceries');
        $task_repository->create('user_1', 'Call doctor');
        $task_repository->create('user_2', 'Do laundry');

        $response = $this->request_simulator
            ->withPath('/task')
            ->withMethod('GET')
            ->withQueryParam('user_uid', 'user_1')
            ->dispatch();

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Your Tasks', $body);
        $this->assertStringContainsString('Buy groceries', $body);
        $this->assertStringContainsString('Call doctor', $body);
        $this->assertStringNotContainsString('Do laundry', $body);
    }

    public function testController_ShowsEmptyStateWhenNoTasks(): void {
        $response = $this->request_simulator
            ->withPath('/task')
            ->withMethod('GET')
            ->withQueryParam('user_uid', 'user_1')
            ->dispatch();

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Your Tasks', $body);
        $this->assertStringContainsString("You haven't added any tasks yet.", $body);
    }

    public function testController_ShowsTaskStatusBadges(): void {
        $task_repository = TaskRepositoryResolver::resolve();
        $task_repository->create('user_1', 'Task 1');
        
        $all_tasks = $task_repository->findByUserUid('user_1');
        $task_id = $all_tasks[0]->id;
        $task_repository->completeTask($task_id);

        $response = $this->request_simulator
            ->withPath('/task')
            ->withMethod('GET')
            ->withQueryParam('user_uid', 'user_1')
            ->dispatch();

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('DONE', $body);
        $this->assertStringContainsString('status-done', $body);
    }

}
