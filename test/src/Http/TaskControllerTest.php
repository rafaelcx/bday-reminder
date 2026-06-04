<?php

declare(strict_types=1);

namespace Test\Src\Http;

use App\Repository\Task\TaskRepositoryResolver;
use App\Repository\Task\TaskStatus;
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
        $this->assertStringContainsString(TaskStatus::DONE->value, $body);
        $this->assertStringContainsString('status-done', $body);
    }

    public function testController_CreatesTask_WhenSuccessful(): void {
        $request_post_params = [
            'title' => 'Read a book',
            'user_uid' => 'user_1',
        ];

        $response = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/task')
            ->withPostParams($request_post_params)
            ->dispatch();

        $task_repository = TaskRepositoryResolver::resolve();
        $task_list = $task_repository->findByUserUid('user_1');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/task?user_uid=user_1', $response->getHeaderLine('Location'));
        $this->assertCount(1, $task_list);
        $this->assertSame('Read a book', $task_list[0]->title);
        $this->assertSame(TaskStatus::DOING, $task_list[0]->status);
    }

    public function testController_CompletesTask_WhenSuccessful(): void {
        $task_repository = TaskRepositoryResolver::resolve();
        $task_repository->create('user_1', 'Task to complete');

        $all_tasks = $task_repository->findByUserUid('user_1');
        $task_id = $all_tasks[0]->id;

        $request_post_params = [
            'task_id' => $task_id,
            'user_uid' => 'user_1',
        ];

        $response = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/task/complete')
            ->withPostParams($request_post_params)
            ->dispatch();

        $updated_tasks = $task_repository->findByUserUid('user_1');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/task?user_uid=user_1', $response->getHeaderLine('Location'));
        $this->assertCount(1, $updated_tasks);
        $this->assertSame(TaskStatus::DONE, $updated_tasks[0]->status);
    }

    public function testController_DeletesTask_WhenSuccessful(): void {
        $task_repository = TaskRepositoryResolver::resolve();
        $task_repository->create('user_1', 'Task to delete');

        $all_tasks = $task_repository->findByUserUid('user_1');
        $task_id = $all_tasks[0]->id;

        $request_post_params = [
            'task_id' => $task_id,
            'user_uid' => 'user_1',
        ];

        $response = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/task/delete')
            ->withPostParams($request_post_params)
            ->dispatch();

        $remaining_tasks = $task_repository->findByUserUid('user_1');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/task?user_uid=user_1', $response->getHeaderLine('Location'));
        $this->assertCount(0, $remaining_tasks);
    }

    public function testController_NotifiesUser_WhenSuccessful(): void {
        $request_post_params = [
            'user_uid' => 'user_1',
        ];

        $response = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/task/notify')
            ->withPostParams($request_post_params)
            ->dispatch();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/task?user_uid=user_1', $response->getHeaderLine('Location'));
    }

}
