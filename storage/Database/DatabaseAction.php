<?php

declare(strict_types=1);

namespace App\Storage\Database;

interface DatabaseAction {

    public function up(): void;

    public function down(): void;

}
