<?php

namespace App\Http\Controllers;

use App\FileUpload;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use FileUpload;

    protected $rootFolder;

    public function __construct()
    {
        $this->rootFolder = 'images/products';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::with('category')->get();

            return response()->json([
                "message" => "Products retrieved successfully",
                "data" => $products
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
                'category_id' => 'required|exists:categories,id',
                'code' => 'required|unique:products',
                'name' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'unit' => 'required|in:kg,lusin,liter,pack,gram',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $imageFile = null;
                if ($request->hasFile('image')) {
                    $imageFile = $this->handleFile($this->rootFolder, 'new', $request->file('image'));
                }

                $product = Product::create([
                    'category_id' => $request->category_id,
                    'code' => $request->code,
                    'name' => $request->name,
                    'slug' => str($request->name)->slug(),
                    'image' => $imageFile,
                    'unit' => $request->unit,
                    'description' => $request->description,
                ]);

                return response()->json([
                    "message" => "Product created successfully",
                    "data" => $product
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Product not found",
            ], 404);
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
            $product = Product::with('category')->findOrFail($id);

            return response()->json([
                "message" => "Product retrieved successfully",
                "data" => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Product not found",
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
                'category_id' => 'required|exists:categories,id',
                'code' => 'required|unique:products,code,' . $id,
                'name' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'unit' => 'required|in:kg,lusin,liter,pack,gram',
                'description' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $product = Product::findOrFail($id);

                $imageFile = $product->image;
                if ($request->hasFile('image')) {
                    $imageFile = $this->handleFile($this->rootFolder, 'update', $request->file('image'), $product->image);
                }

                $product->update([
                    'category_id' => $request->category_id,
                    'code' => $request->code,
                    'name' => $request->name,
                    'slug' => str($request->name)->slug(),
                    'image' => $imageFile,
                    'unit' => $request->unit,
                    'description' => $request->description,
                ]);

                return response()->json([
                    "message" => "Product updated successfully",
                    "data" => $product
                ], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Product not found",
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
            $product = Product::findOrFail($id);

            if ($product->image) {
                $this->handleFile($this->rootFolder, 'delete', null, $product->image);
            }

            $product->delete();

            return response()->json([
                "message" => "Product deleted successfully",
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Product not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
