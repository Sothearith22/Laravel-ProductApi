<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $product = Product::all();

        return response()->json([
            'status' => 'success',
            'products' => $product,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'price' => ['required', 'numeric', 'min:1'],
                'qty' => ['required', 'integer', 'min:1'],
                'status' => 'required|boolean',
                'image' => ['required', 'image', 'max:2048'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // UPLOAD IMAGE TO CLOUDINARY
            $upload = $request->file('image')->storeOnCloudinary('products');

            //  SAVE PRODUCT
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
                'qty' => $request->qty,
                'status' => $request->status,
                'image_url' => $upload->getSecurePath(),
                'image_public_id' => $upload->getPublicId(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (! $product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'product' => $product,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => ['sometimes', 'string', 'max:255'],
                'price' => ['sometimes', 'numeric', 'min:1'],
                'qty' => ['sometimes', 'integer', 'min:1'],
                'status' => ['sometimes', 'in:1,0'],
                'image' => ['sometimes', 'image', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Initialize data to update
            $dataToUpdate = [
                'name' => $request->name,
                'price' => $request->price,
                'qty' => $request->qty,
                'status' => $request->status,
            ];

            // FIX 3: Use $request->hasFile(), NOT $validator->hasFile()
            if ($request->hasFile('image')) {
                // Delete old image first
                if ($product->image_public_id) {
                    cloudinary()->destroy($product->image_public_id);
                }

                // Upload new image
                $result = $request->file('image')->storeOnCloudinary('products');

                // Add image data to the update array
                $dataToUpdate['image_url'] = $result->getSecurePath();
                $dataToUpdate['image_public_id'] = $result->getPublicId();
            }

            // Update the product with the prepared data
            $product->update($dataToUpdate);

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'product' => $product,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong during update',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product Not found',
            ], 404);
        }
        
        if ($product->image_public_id) {
            cloudinary()->destroy($product->image_public_id);
        }

        // Delete record from database
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ], 200);

    }
}
