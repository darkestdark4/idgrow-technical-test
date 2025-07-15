<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();

            return response()->json([
                "message" => "Users retrieved successfully",
                "data" => $users
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                return response()->json([
                    "message" => "User created successfully",
                    "data" => $user
                ], 201);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                "message" => "User retrieved successfully",
                "data" => $user
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "User not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $user = User::findOrFail($id);

                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                return response()->json([
                    "message" => "User updated successfully",
                    "data" => $user
                ], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "User not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return response()->json([
                "message" => "User deleted successfully",
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "User not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
