<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\JWTAUth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

    public function handle(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password_hash' => 'required'
        ]);

        try {

            // instance new user
            $user = new User;

            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password_hash = Hash::make($request->input('password'));

            // save user
            $user->save();

        } catch(\Exception $e) {
            return respose()->json(['status' => 400, 'message' => 'User registration is failed']);
        }

        return response()->json(['status' => 200, 'message' => 'User created!']);
    }

    
}
