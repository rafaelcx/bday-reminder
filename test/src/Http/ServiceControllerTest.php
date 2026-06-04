<?php

declare(strict_types=1);

namespace Test\Src\Http;

use Test\CustomTestCase;

class ServiceControllerTest extends CustomTestCase {

    public function testController_Show_WhenSuccessful(): void {
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withPath('/services')
            ->withQueryParam('user_uid', '123')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $result_body = (string) $result->getBody();
        $this->assertStringContainsString('Services', $result_body);
        $this->assertStringContainsString('<input type="hidden" name="user_uid" value="123">', $result_body);
        $this->assertStringContainsString('Birthday', $result_body);
    }

    public function testController_Show_WhenUidIncludesSpecialCharacters(): void {
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withPath('/services')
            ->withQueryParam('user_uid', 'user"&<>')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $result_body = (string) $result->getBody();
        $this->assertStringContainsString('Services', $result_body);
        $this->assertStringContainsString('value="user&quot;&amp;&lt;&gt;"', $result_body);
    }

}
