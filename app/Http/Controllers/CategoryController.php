<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();

            return response()->json([
                "message" => "Categories retrieved successfully",
                "data" => $categories
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $category = Category::create([
                    'name' => $request->name,
                    'slug' => str($request->name)->slug(),
                ]);

                return response()->json([
                    "message" => "Category created successfully",
                    "data" => $category
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
            $category = Category::findOrFail($id);

            return response()->json([
                "message" => "Category retrieved successfully",
                "data" => $category
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Category not found",
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $category = Category::findOrFail($id);

                $category->update([
                    'name' => $request->name,
                    'slug' => str($request->name)->slug(),
                ]);

                return response()->json([
                    "message" => "Category updated successfully",
                    "data" => $category
                ], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Category not found",
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
            $category = Category::findOrFail($id);

            $category->delete();

            return response()->json([
                "message" => "Category deleted successfully",
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Category not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
