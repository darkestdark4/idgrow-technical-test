<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use App\Models\ProductLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MutationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->query('user');
            $product = $request->query('product');
            $location = $request->query('location');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $type = $request->query('type');

            $mutations = Mutation::with(['user', 'productLocation.product']);

            if ($user) {
                $mutations = $mutations->where('user_id', $user);
            }

            if ($product) {
                $mutations = $mutations->whereHas('productLocation', function ($query) use ($product) {
                    $query->where('product_id', $product);
                });
            }

            if ($location) {
                $mutations = $mutations->whereHas('productLocation', function ($query) use ($location) {
                    $query->where('location_id', $location);
                });
            }

            if ($startDate && $endDate) {
                $mutations = $mutations->whereBetween('created_at', [$startDate, $endDate]);
            }

            if ($type) {
                $mutations = $mutations->where('type', $type);
            }

            $mutations = $mutations->orderBy('created_at', 'desc')->get();

            $result = $mutations->map(function ($item) {
                return [
                    'id' => $item->id,
                    'date' => $item->created_at,
                    'type' => $item->type,
                    'user' => $item->user->name,
                    'product' => $item->productLocation->product->name,
                    'location' => $item->productLocation->location->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes
                ];
            });

            return response()->json([
                "message" => "Success",
                "data" => $result
            ], 200);
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
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'product_location' => 'required|exists:product_locations,id',
                'type' => 'required|in:in,out',
                'quantity' => 'required|numeric|min:1',
                'notes' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $userId = $request->user()->id;

                // check and update stock in product location table
                $productLocation = ProductLocation::find($request->product_location);
                if ($request->type == 'out') {
                    // check available stock and request quantity
                    if ($productLocation->stock < $request->quantity) {
                        return response()->json([
                            "message" => "Stock not enough",
                        ], 400);
                    }

                    $productLocation->stock -= $request->quantity;
                    $productLocation->save();
                } else if ($request->type == 'in') {
                    $productLocation->stock += $request->quantity;
                    $productLocation->save();
                }

                $mutation = Mutation::create([
                    'product_location_id' => $request->product_location,
                    'user_id' => $userId,
                    'type' => $request->type,
                    'quantity' => $request->quantity,
                    'notes' => $request->notes,
                ]);

                DB::commit();
                return response()->json([
                    "message" => "Success",
                    "data" => $mutation
                ], 201);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
