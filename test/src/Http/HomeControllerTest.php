<?php

declare(strict_types=1);

namespace Test\Http;

use App\Repository\User\UserRepositoryResolver;
use Test\CustomTestCase;

class HomeControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $user_respository = UserRepositoryResolver::resolve();
        $user_respository->create('Name One');
        $user_respository->create('Name Two');
        
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withBody([])
            ->withPath('/')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $expected_html_option_tag_1 = '<option value=Name One>Name One</option>';
        $expected_html_option_tag_2 = '<option value=Name Two>Name Two</option>';
        $this->assertStringContainsString($expected_html_option_tag_1, (string) $result->getBody());
        $this->assertStringContainsString($expected_html_option_tag_2, (string) $result->getBody());
    }

}
