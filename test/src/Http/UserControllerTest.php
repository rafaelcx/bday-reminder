<?php

declare(strict_types=1);

namespace Test\Src\Http;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use Test\CustomTestCase;

class UserControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $birthday_repository = BirthdayRepositoryResolver::resolve();
        $birthday_repository->create('1', 'Name One', new \DateTime());
        $birthday_repository->create('1', 'Name Two', new \DateTime());
        $birthday_repository->create('2', 'Name Three', new \DateTime());
       
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withPath('/user')
            ->withQueryParam('uid', '1')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $result_body = (string) $result->getBody();
        $this->assertStringContainsString('Your Birthday List', $result_body);
        $this->assertStringContainsString('Name One', $result_body);
        $this->assertStringContainsString('Name Two', $result_body);
        $this->assertStringNotContainsString('Name Three', $result_body);
    }

    public function testController_WhenSuccessful_AndUserHasNoBirthdays(): void {
        $result = $this->request_simulator
            ->withMethod('GET')
            ->withPath('/user')
            ->withQueryParam('uid', '1')
            ->dispatch();

        $this->assertSame(200, $result->getStatusCode());

        $result_body = (string) $result->getBody();
        $this->assertStringContainsString('Your Birthday List', $result_body);
        $this->assertStringContainsString('You haven\'t added any birthdays yet', $result_body);
    }

}
