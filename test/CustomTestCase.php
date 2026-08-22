<?php

declare(strict_types=1);

namespace Test;

use App\Utils\Clock;
use App\Utils\Environment;
use App\Utils\EnvironmentType;
use App\Utils\StaticScope;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Test\Support\Files\Service\FileServiceResolverForTests;
use Test\Support\Http\Client\HttpClientForTests;
use Test\Support\Http\RequestSimulator;
use Test\Support\Logger\ProcessLogContextForTests;

class CustomTestCase extends TestCase {

    public RequestSimulator $request_simulator;

    #[Before]
    public function tagTestEnvironment(): void {
        StaticScope::set(Environment::class, 'env', EnvironmentType::TEST);
    }

    #[Before]
    public function setUpFakers(): void {
        $this->request_simulator = new RequestSimulator();
    }

    #[Before]
    public function setUpOverrides(): void {
        FileServiceResolverForTests::override();
        HttpClientForTests::override();
    }

    #[After]
    public function resetOverrides(): void {
        StaticScope::clear();
        FileServiceResolverForTests::reset();
        ProcessLogContextForTests::reset();
        Clock::reset();
    }

}
