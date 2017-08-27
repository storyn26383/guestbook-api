<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function store($id, Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
        ]);

        $post = Post::findOrFail($id);

        $reply = $post->children()->create(array_merge($request->all(), ['user_id' => $request->user()->id]));

        return ['id' => $reply->id];
    }
}
