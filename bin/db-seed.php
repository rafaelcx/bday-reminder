#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Storage\Database\DatabaseActionEngine;
use App\Storage\Database\Seed\SeedRegistry;

$seed_actions = new SeedRegistry()->getRegisteredSeeds();
$seed_engine = new DatabaseActionEngine(...$seed_actions);
$seed_engine->execute();
