<?php

namespace App\Containers\User\UI\API\Tests\Functional;

use App\Containers\User\Tests\TestCase;

/**
 * Class UpdateUserTest.
 *
 * @author Mahmoud Zalt <mahmoud@zalt.me>
 */
class UpdateUserTest extends TestCase
{

    protected $endpoint = 'put@v1/users/{id}';

    protected $access = [
        'roles'       => '',
        'permissions' => 'update-users',
    ];

    public function testUpdateExistingUser_()
    {
        $user = $this->getTestingUser();

        $data = [
            'name'     => 'Updated Name',
            'password' => 'updated#Password',
        ];

        // send the HTTP request
        $response = $this->injectId($user->id)->makeCall($data);

        // assert response status is correct
        $response->assertStatus(200);

        // assert returned user is the updated one
        $this->assertResponseContainKeyValue([
            'object' => 'User',
            'email' => $user->email,
            'name'  => $data['name'],
        ]);

        // assert data was updated in the database
        $this->assertDatabaseHas('users', ['name' => $data['name']]);
    }

    public function testUpdateNonExistingUser_()
    {
        $data = [
            'name' => 'Updated Name',
        ];

        $fakeUserId = 7777;

        // send the HTTP request
        $response = $this->injectId($fakeUserId)->makeCall($data);

        // assert response status is correct
        $response->assertStatus(400);

        $this->assertResponseContainKeyValue([
            'errors' => 'Oops something went wrong.'
        ]);
    }

    public function testUpdateExistingUserWithoutData_()
    {
        // send the HTTP request
        $response = $this->makeCall();

        // assert response status is correct
        $response->assertStatus(417);

        $this->assertResponseContainKeyValue([
            'message' => 'Inputs are empty.'
        ]);
    }

    public function testUpdateExistingUserWithEmptyValues()
    {
        $data = [
            'name'     => '',
            'password' => '',
        ];

        // send the HTTP request
        $response = $this->makeCall($data);

        // assert response status is correct
        $response->assertStatus(422);

        $this->assertValidationErrorContain([
            // messages should be updated after modifying the validation rules, to pass this test
            'password' => 'The password must be at least 6 characters.',
            'name' => 'The name must be at least 2 characters.',
        ]);

    }
}
