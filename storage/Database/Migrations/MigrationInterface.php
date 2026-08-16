<?php

declare(strict_types=1);

namespace App\Storage\Database\Migrations;

interface MigrationInterface {

    public function up(): void;

    public function down(): void;

}
