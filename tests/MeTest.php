<?php

namespace Tests;

use App\User;
use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class MeTest extends TestCase
{
    use DatabaseMigrations;

    public function testMe()
    {
        $me = [
            'name' => 'Sasaya',
            'email' => 'sasaya@example.com',
        ];

        $user = factory(User::class)->create($me);

        $this->actingAs($user);

        $this->json('get', '/api/me')
             ->seeJson($me);
    }

    public function testMeWithoutLogin()
    {
        $this->json('get', '/api/me')
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }
}
