<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);

        $request->merge([
            'password' => app('hash')->make($request->input('password'))
        ]);

        User::create($request->all());

        return ['success' => true];
    }

    public function login(Request $request)
    {
        try {
            $user = User::whereEmail($request->input('email'))->firstOrFail();

            if (! app('hash')->check($request->input('password'), $user->password)) {
                throw new Exception;
            }
        } catch (Exception $e) {
            return response()->json(['error' => ['message' => 'Unauthorized.']], 401);
        }

        $user->login();

        return ['api_token' => $user->api_token];
    }

    public function logout(Request $request)
    {
        $request->user()->logout();

        return ['success' => true];
    }
}
