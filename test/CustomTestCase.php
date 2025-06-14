<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Test\Support\FileServiceResolverForTests;
use Test\Support\Http\RequestSimulator;
use Test\Support\Repository\Birthday\BirthdayRepositoryResolverForTests;
use Test\Support\Repository\User\UserRepositoryResolverForTests;

class CustomTestCase extends TestCase {

    public RequestSimulator $request_simulator;

    public function __construct() {
        parent::__construct();
        $this->request_simulator = new RequestSimulator();
    }

    /** @before */
    public function setUpOverrides(): void {
        FileServiceResolverForTests::override();
    }

    /** @after */
    public function resetOverrides(): void {
        FileServiceResolverForTests::reset();
        BirthdayRepositoryResolverForTests::reset();
        UserRepositoryResolverForTests::reset();
    }

}
