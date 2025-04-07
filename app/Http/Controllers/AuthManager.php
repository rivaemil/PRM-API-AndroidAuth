<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;    

class AuthManager extends Controller
{
    function register(Request $request)
    {
        // Validate the request data
        $validate = Validator::make($request->all(), 
            [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:8|confirmed',
            ]);

        if ($validate->fails()) {
            return response()->json(
                ["status"=>"error",
                "message"=>$validate->errors()->getMessages()
                ], 200);
        }


        $validated = $validate->validated();

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']); // Hash the password before saving
        if ($user->save()){
            return response()->json(
                [
                    "status"=>"success",
                    "message"=>"User registered successfully!"
                ], 200);
        }
        else {
            return response()->json(
                [
                "status"=>"error",
                "message"=>"User registration failed!"
                ], 200);
        }
    }

    function login(Request $request)
    {
        // Validate the request data
        $validate = Validator::make($request->all(), 
            [
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
            ]);

        if ($validate->fails()) {
            return response()->json(
                [
                    "status"=>"error",
                    "message"=>$validate->errors()->getMessages()
                ], 200);
        }


        $validated = $validate->validated();

        if(Auth::attempt(
                [
                    'email' => $validated['email'],
                    'password' => $validated['password']
                ]
            )) {
                $user = Auth::user(); 
                $token = $user->createToken('mobile_token')->plainTextToken;
                return response()->json(
                    [
                        "status"=>"success",
                        "data"=> [
                            'user'=>$user,
                            'token'=>$token
                        ],
                        "message"=>"User logged in successfully!"
                    ], 200);
            } else {
                return response()->json(
                    [
                        "status"=>"error",
                        "message"=>"Invalid credentials!"
                    ], 200);
            }
    }
}
