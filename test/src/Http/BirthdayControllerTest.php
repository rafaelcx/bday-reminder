<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\Birthday\BirthdayRepositoryResolver;
use Test\CustomTestCase;

class BirthdayControllerTest extends CustomTestCase {

    public function testController_Create_WhenSuccessful(): void {
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

    public function testController_Update_WhenSuccessful(): void {
        $birthday_repository = BirthdayRepositoryResolver::resolve();
        $birthday_repository->create('1', 'Jhon', new \DateTime('1900-01-01'));

        $target_bday_uid = $birthday_repository->findByUserUid('1')[0]->uid;

        $request_post_params = [
            'name' => 'New Name',
            'date' => '2000-01-01',
            'birthday_uid' => $target_bday_uid,
            'user_uid' => '1',
        ];
        
        $result = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/birthday/edit')
            ->withPostParams($request_post_params)
            ->dispatch();

        $birthday_list = $birthday_repository->findByUserUid('1');

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/user?uid=1', $result->getHeaderLine('Location'));
        $this->assertCount(1, $birthday_list);
        $this->assertSame('New Name', $birthday_list[0]->name);
        $this->assertSame('2000-01-01', $birthday_list[0]->date->format('Y-m-d'));
    }

    public function testController_Delete_WhenSuccessful(): void {
        $birthday_repository = BirthdayRepositoryResolver::resolve();
        $birthday_repository->create('1', 'Jhon', new \DateTime('1900-01-01'));

        $target_bday_uid = $birthday_repository->findByUserUid('1')[0]->uid;

        $request_post_params = [
            'birthday_uid' => $target_bday_uid,
            'user_uid' => '1',
        ];
        
        $result = $this->request_simulator
            ->withMethod('POST')
            ->withPath('/birthday/delete')
            ->withPostParams($request_post_params)
            ->dispatch();

        $birthday_list = $birthday_repository->findByUserUid('1');

        $this->assertSame(302, $result->getStatusCode());
        $this->assertSame('/user?uid=1', $result->getHeaderLine('Location'));
        $this->assertCount(0, $birthday_list);
    }

}
