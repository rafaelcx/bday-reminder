<?php

declare(strict_types=1);

namespace Test;

use App\Utils\StaticScope;
use PHPUnit\Framework\TestCase;
use Test\Support\FileServiceResolverForTests;
use Test\Support\Http\RequestSimulator;
use Test\Support\Logger\ProcessContextForTests;
use Test\Support\Repository\Birthday\BirthdayRepositoryResolverForTests;
use Test\Support\Repository\User\UserRepositoryResolverForTests;

class CustomTestCase extends TestCase {

    public RequestSimulator $request_simulator;

    /** @before */
    public function setUpFakers(): void {
        $this->request_simulator = new RequestSimulator();
    }

    /** @before */
    public function setUpOverrides(): void {
        FileServiceResolverForTests::override();
    }

    /** @after */
    public function resetOverrides(): void {
        StaticScope::clear();
        FileServiceResolverForTests::reset();
        BirthdayRepositoryResolverForTests::reset();
        UserRepositoryResolverForTests::reset();
        ProcessContextForTests::reset();
    }

}
