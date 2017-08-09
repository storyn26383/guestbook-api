<?php

namespace Tests;

use App\User;
use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    public function testRegister()
    {
        $user = [
            'name' => 'Sasaya',
            'email' => 'sasaya@example.com',
            'password' => 'sasaya',
        ];

        $this->json('post', '/api/register', $user)
             ->seeJson(['success' => true]);

        $this->seeInDatabase('users', array_intersect_key($user, array_flip(['name', 'email'])));
    }

    public function testRegisterWithDuplicateEmail()
    {
        factory(User::class)->create(['email' => 'sasaya@example.com']);

        $user = [
            'name' => 'Sasaya',
            'email' => 'sasaya@example.com',
            'password' => 'sasaya',
        ];

        $this->json('post', '/api/register', $user)
             ->seeJson(['email' => ['The email has already been taken.']]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }

    public function testRegisterWithoutParams()
    {
        $this->json('post', '/api/register')
             ->seeJson([
                 'name' => ['The name field is required.'],
                 'email' => ['The email field is required.'],
                 'password' => ['The password field is required.'],
             ]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }

    public function testLogin()
    {
        factory(User::class)->create([
            'email' => $email = 'sasaya@example.com',
            'password' => app('hash')->make($password = 'sasaya'),
        ]);

        $this->json('post', '/api/login', compact('email', 'password'))
             ->seeJsonStructure(['api_token']);

        $api_token = $this->response->getOriginalContent()['api_token'];

        $this->seeInDatabase('users', compact('email', 'api_token'));
    }

    public function testLoginWithoutParams()
    {
        $this->json('post', '/api/login')
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function testLoginWithWrongEmail()
    {
        $this->json('post', '/api/login', ['email' => 'wrong', 'password' => 'correct'])
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function testLoginWithWrongPassword()
    {
        factory(User::class)->create([
            'email' => $email = 'sasaya@example.com',
        ]);

        $this->json('post', '/api/login', ['email' => $email, 'password' => 'wrong'])
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function testLogout()
    {
        $user = factory(User::class)->create()->login();

        $this->json('get', '/api/logout', [], ['API_TOKEN' => $user->api_token])
             ->seeJson(['success' => true]);

        $this->assertNull($user->fresh()->api_token);
    }

    public function testLogoutWithoutLogin()
    {
        $this->json('get', '/api/logout')
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }
}
