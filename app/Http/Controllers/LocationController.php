<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $locations = Location::all();

            return response()->json([
                "message" => "Locations retrieved successfully",
                "data" => $locations
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
                'code' => 'required|unique:locations',
                'name' => 'required',
                'address' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $location = Location::create([
                    'code' => $request->code,
                    'name' => $request->name,
                    'address' => $request->address
                ]);

                return response()->json([
                    "message" => "Location created successfully",
                    "data" => $location
                ]);
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
            $location = Location::findOrFail($id);

            return response()->json([
                "message" => "Location retrieved successfully",
                "data" => $location
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Location not found",
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
                'code' => 'required|unique:locations,code,' . $id,
                'name' => 'required',
                'address' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Error validation",
                    "errors" => $validator->errors()
                ], 400);
            } else {
                $location = Location::findOrFail($id);
                $location->update([
                    'code' => $request->code,
                    'name' => $request->name,
                    'address' => $request->address
                ]);

                return response()->json([
                    "message" => "Location updated successfully",
                    "data" => $location
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Location not found",
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
            $location = Location::findOrFail($id);
            $location->delete();

            return response()->json([
                "message" => "Location deleted successfully",
                "data" => $location
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                "message" => "Location not found",
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage(),
            ]);
        }
    }
}
