<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Task\TaskRepositoryResolver;
use App\Services\Task\TaskService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class TaskController {

    public function show(Request $request, Response $response): Response {
        $user_uid = $request->getQueryParams()['user_uid'];

        $task_list = TaskRepositoryResolver::resolve()
            ->findByUserUid($user_uid);

        $view = Twig::fromRequest($request);
        return $view->render($response, 'task.html.twig', [
            'tasks' => $task_list,
            'user_uid' => $user_uid,
        ]);
    }

    public function create(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();
        if (!is_array($parsed_body)) {
            throw new \InvalidArgumentException('Invalid request body.');
        }

        $task_title = $parsed_body['title'];
        $task_user_uid = $parsed_body['user_uid'];

        TaskRepositoryResolver::resolve()
            ->create($task_user_uid, $task_title);

        return $this->buildRedirectResponse($response, $task_user_uid);
    }

    public function complete(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();
        if (!is_array($parsed_body)) {
            throw new \InvalidArgumentException('Invalid request body.');
        }

        $task_id = $parsed_body['task_id'];
        $task_user_uid = $parsed_body['user_uid'];

        TaskRepositoryResolver::resolve()
            ->completeTask($task_id);

        return $this->buildRedirectResponse($response, $task_user_uid);
    }

    public function delete(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();
        if (!is_array($parsed_body)) {
            throw new \InvalidArgumentException('Invalid request body.');
        }

        $task_id = $parsed_body['task_id'];
        $task_user_uid = $parsed_body['user_uid'];

        TaskRepositoryResolver::resolve()
            ->delete($task_id);

        return $this->buildRedirectResponse($response, $task_user_uid);
    }

    public function notify(Request $request, Response $response): Response {
        $parsed_body = $request->getParsedBody();
        if (!is_array($parsed_body)) {
            throw new \InvalidArgumentException('Invalid request body.');
        }

        $user_uid = $parsed_body['user_uid'];

        new TaskService()->notify();

        return $this->buildRedirectResponse($response, $user_uid);
    }

    private function buildRedirectResponse(Response $response, string $user_uid): Response {
        return $response
            ->withStatus(302)
            ->withHeader('Location', '/task?user_uid=' . urlencode($user_uid));
    }

}
