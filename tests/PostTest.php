<?php

namespace Tests;

use App\Post;
use App\User;
use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PostTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreate()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $post = [
            'content' => 'Hello World',
        ];

        $this->json('post', '/api/posts', $post)
             ->seeJson(['success' => true]);

        $this->seeInDatabase('posts', $post);
    }

    public function testCreateWithoutParams()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $this->json('post', '/api/posts')
             ->seeJson([
                 'content' => ['The content field is required.'],
             ]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }

    public function testCreateWithoutLogin()
    {
        $this->json('post', '/api/posts')
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());
    }

    public function testUpdate()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $content = 'Hello World';

        $this->json('put', "/api/posts/{$post->id}", compact('content'))
             ->seeJson(['success' => true]);

        $this->assertEquals($content, $post->fresh()->content);
    }

    public function testUpdateWithoutParams()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->json('put', "/api/posts/{$post->id}")
             ->seeJson([
                 'content' => ['The content field is required.'],
             ]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }

    public function testUpdateNotByAuthor()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $this->actingAs($user);

        $content = 'Hello World';

        $this->json('put', "/api/posts/{$post->id}", compact('content'))
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());

        $this->assertNotEquals($content, $post->fresh()->content);
    }

    public function testDelete()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->json('delete', "/api/posts/{$post->id}")
             ->seeJson(['success' => true]);

        $this->assertNull($post->fresh());
    }

    public function testDeleteNotByAuthor()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $this->actingAs($user);

        $this->json('delete', "/api/posts/{$post->id}")
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());

        $this->assertNotNull($post->fresh());
    }

    public function testList()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);

        $post = factory(Post::class)->create();
        $reply = factory(Post::class)->create();
        $nestedReply = factory(Post::class)->create();

        $post->appendNode($reply);
        $reply->appendNode($nestedReply);

        $this->json('get', "/api/posts");

        $this->assertArraySubset([[
            'content' => $post->content,
            'children' => [[
                'content' => $reply->content,
                'children' => [[
                    'content' => $nestedReply->content,
                    'children' => []
                ]],
            ]],
        ]], $this->response->getOriginalContent()->toArray());
    }
}
