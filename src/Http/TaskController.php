<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Task\TaskRepositoryResolver;
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

}
