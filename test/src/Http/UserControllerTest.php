<?php

declare(strict_types=1);

namespace Test\Src\Http;

use Test\CustomTestCase;

class UserControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withBody([])
            ->withPath('/user')
            ->withQueryParam('uid', '1')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $result_body = (string) $result->getBody();
        $this->assertStringContainsString('Your Birthday List', $result_body);
        $this->assertStringContainsString('Rafael Garcia de Carvalho e Outro Sobrenome e Outro', $result_body);
    }

}
