<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store($id, Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
        ]);

        $post = Post::findOrFail($id);

        $post->children()->create(array_merge($request->all(), ['user_id' => $request->user()->id]));

        return ['success' => true];
    }
}
