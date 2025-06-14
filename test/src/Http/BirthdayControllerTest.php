<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use Test\CustomTestCase;

class BirthdayControllerTest extends CustomTestCase {

    public function testController_WhenSuccessful(): void {
        $request_post_params = [
            'name' => 'Jhon',
            'date' => '2000-01-01',
            'user_id' => '123',
        ];
        
        $result = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/birthday')
            ->withPostParams($request_post_params)
            ->dispatch();

        $birthday_repository = BirthdayRepositoryResolver::resolve();
        $birthday_list = $birthday_repository->findByUserUid('123');

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/user?uid=123', $result->getHeaderLine('Location'));
        $this->assertCount(1, $birthday_list);
        $this->assertSame('Jhon', $birthday_list[0]->name);
    }

}
