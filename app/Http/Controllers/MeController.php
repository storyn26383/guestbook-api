<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->setVisible(['name', 'email']);
    }
}
