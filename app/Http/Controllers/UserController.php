<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    function login()
    {
        $credentials = request(['email', 'password']);
        if (!auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $user = auth()->user();
        $token = $user->createToken('authToken')->accessToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    function signup()
    {
        $user = new User();
        $user->email = request('email');
        $user->name = request('name');
        $user->password = bcrypt(request('password'));
        $user->save();
        return $this->login();
    }

    function logout()
    {
        auth()->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

}
