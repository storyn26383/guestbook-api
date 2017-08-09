<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function index()
    {
        return Post::with('user')->get()->map(function ($post) {
            $post->user->setVisible(['name', 'email']);

            return $post;
        })->toTree();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
        ]);

        $request->user()->posts()->create($request->all());

        return ['success' => true];
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
        ]);

        $post = Post::findOrFail($id);

        if (!$post->user->is($request->user())) {
            return response()->json(['error' => ['message' => 'Unauthorized.']], 401);
        }

        $post->update($request->all());

        return ['success' => true];
    }

    public function destroy($id, Request $request)
    {
        $post = Post::findOrFail($id);

        if (!$post->user->is($request->user())) {
            return response()->json(['error' => ['message' => 'Unauthorized.']], 401);
        }

        $post->delete();

        return ['success' => true];
    }
}
