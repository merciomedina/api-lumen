<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\JWTAUth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SessionController extends Controller
{

    protected $jwt;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request) {
        
        $this->validate($request, [
            'email' => 'required|string',
            'password_hash' => 'required|string'
        ]);

        if (! $token = $this->jwt->attempt($request->only(['email', 'password_hash']))) {
            return response()->json(['status' => 404, 'message' => 'User not found']);
        }

        return response()->json(compact('token'));
    }
}
