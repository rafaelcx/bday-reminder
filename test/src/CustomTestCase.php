<?php

declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use Test\Support\Http\RequestSimulator;

class CustomTestCase extends TestCase {

    public RequestSimulator $request_simulator;

    public function __construct() {
        parent::__construct();
        $this->request_simulator = new RequestSimulator();
    }

}
