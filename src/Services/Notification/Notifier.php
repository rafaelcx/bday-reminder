<?php

declare(strict_types=1);

namespace App\Services\Notification;

interface Notifier {

    public function notify(): void;

}
