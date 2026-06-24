<?php

declare(strict_types=1);

namespace App\Services\Interaction;

use App\Services\Birthday\BirthdayService;
use App\Services\Task\TaskService;

class InteractionService {

    public function process(): void {
        foreach ($this->getInteractors() as $interactor) {
            $interactor->processInteractions();
        }
    }

    /**
     * @return Interactor[]
     */
    private function getInteractors(): array {
        return [
            new BirthdayService(),
            new TaskService(),
        ];
    }

}
