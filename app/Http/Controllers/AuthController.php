<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $user = User::create([
                    "name" => $request->name,
                    "email" => $request->email,
                    "password" => Hash::make($request->password),
                ]);

                // generate token
                $token = $user->createToken($user->email)->plainTextToken;

                return response()->json([
                    "message" => "Register successfully",
                    "data" => [
                        "token" => $token
                    ]
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $user = User::where('email', $request->email)->first();

                // check password
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return response()->json([
                        "message" => "Email or password is incorrect",
                    ], 401);
                } else {
                    // generate token
                    $token = $user->createToken($user->email)->plainTextToken;

                    return response()->json([
                        "message" => "Login successfully",
                        "data" => [
                            "token" => $token
                        ]
                    ], 200);
                }
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "message" => "Logout successfully",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
