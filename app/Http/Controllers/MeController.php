<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function index(Request $request)
    {
        return array_intersect_key($request->user()->toArray(), array_flip(['name', 'email']));
    }
}
