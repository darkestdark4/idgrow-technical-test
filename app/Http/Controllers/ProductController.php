<?php

namespace App\Http\Controllers;

use App\FileUpload;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Matcher\Not;

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
            $products = Product::with(['category', 'productLocations.mutations'])->get();

            $result = $products->map(function ($product) {
                // define product location
                $productLocation = $product->productLocations->map(function ($item) {
                    // define mutations
                    $mutation = $item->mutations->map(function ($mutation) {
                        return [
                            'id' => $mutation->id,
                            'type' => $mutation->type,
                            'quantity' => $mutation->quantity,
                            'date' => $mutation->created_at
                        ];
                    });

                    return [
                        'id' => $item->id,
                        'location' => $item->location->name,
                        'stock' => $item->stock,
                        'mutation' => $mutation
                    ];
                });

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'unit' => $product->unit,
                    'description' => $product->description,
                    'image' => $product->image,
                    'category' => $product->category->name,
                    'location' => $productLocation
                ];
            });

            // $result = ProductLocation::with(['mutations'])->get();

            return response()->json([
                "message" => "Products retrieved successfully",
                "data" => $result
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
                ], 201);
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
            $product = Product::with(['category', 'productLocations.mutations'])->findOrFail($id);

            $productLocation = $product->productLocations->map(function ($item) {
                // define mutations
                $mutation = $item->mutations->map(function ($mutation) {
                    return [
                        'id' => $mutation->id,
                        'type' => $mutation->type,
                        'quantity' => $mutation->quantity,
                        'date' => $mutation->created_at
                    ];
                });

                return [
                    'id' => $item->id,
                    'location' => $item->location->name,
                    'stock' => $item->stock,
                    'mutation' => $mutation
                ];
            });

            $result = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'unit' => $product->unit,
                'description' => $product->description,
                'image' => $product->image,
                'category' => $product->category->name,
                'location' => $productLocation
            ];

            return response()->json([
                "message" => "Product retrieved successfully",
                "data" => $result
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

    public function add_product_location(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product' => 'required|exists:products,id',
                'location' => 'required|exists:locations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $productLoc = ProductLocation::create([
                    'product_id' => $request->product,
                    'location_id' => $request->location,
                    'stock' => $request->stock
                ]);

                return response()->json([
                    "message" => "Product location added successfully",
                    "data" => $productLoc
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function remove_product_location(string $id)
    {
        try {
            $productLoc = ProductLocation::findOrFail($id);

            $productLoc->delete();

            return response()->json([
                "message" => "Product location removed successfully",
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Product location not found",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
