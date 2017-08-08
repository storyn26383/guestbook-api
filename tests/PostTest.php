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
            'title' => 'Hello World',
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
                 'title' => ['The title field is required.'],
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

        $title = 'Hello World';

        $this->json('put', "/api/posts/{$post->id}", compact('title'))
             ->seeJson(['success' => true]);

        $this->assertEquals($title, $post->fresh()->title);
    }

    public function testUpdateWithoutParams()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $this->json('put', "/api/posts/{$post->id}")
             ->seeJson([
                 'title' => ['The title field is required when content is .'],
                 'content' => ['The content field is required when title is .'],
             ]);

        $this->assertEquals(422, $this->response->getStatusCode());
    }

    public function testUpdateNotByAuthor()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $this->actingAs($user);

        $title = 'Hello World';

        $this->json('put', "/api/posts/{$post->id}", compact('title'))
             ->seeJson(['error' => ['message' => 'Unauthorized.']]);

        $this->assertEquals(401, $this->response->getStatusCode());

        $this->assertNotEquals($title, $post->fresh()->title);
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
}
