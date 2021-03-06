<?php

namespace Tests;

use App\Post;
use App\User;
use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ReplyTest extends TestCase
{
    use DatabaseMigrations;

    public function testReply()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $this->actingAs($user);

        $reply = [
            'content' => 'Hello World',
        ];

        $this->json('post', "/api/posts/{$post->id}/reply", $reply)
             ->seeJsonStructure(['id']);

        $this->seeInDatabase('posts', $reply);

        $this->assertEquals($reply['content'], $post->children()->first()->content);
    }

    public function testReplyWithoutLogin()
    {
        $post = factory(Post::class)->create();

        $this->json('post', "/api/posts/{$post->id}/reply")
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function testReplyWithoutParams()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $this->actingAs($user);

        $this->json('post', "/api/posts/{$post->id}/reply")
             ->seeJson([
                 'content' => ['The content field is required.'],
             ]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }
}
