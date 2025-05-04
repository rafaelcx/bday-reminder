<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Test\Support\Http\RequestSimulator;
use Test\Support\Repository\User\UserRepositoryInMemory;
use Test\Support\Repository\User\UserRepositoryResolverForTests;

class CustomTestCase extends TestCase {

    public RequestSimulator $request_simulator;

    public function __construct() {
        parent::__construct();
        $this->request_simulator = new RequestSimulator();
    }

    public function setUp(): void {
        parent::setUp();
        $this->setupUserRepository();
    }

    private function setupUserRepository(): void {
        $in_memory_repo = new UserRepositoryInMemory();
        $in_memory_repo::$user_list = [];
        UserRepositoryResolverForTests::override($in_memory_repo);
    }

}
