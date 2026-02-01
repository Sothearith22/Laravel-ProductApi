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
            "status"   => "success",
            "products" => $product,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => ['required', 'string', 'max:255'],
            'price'  => ['required', 'numeric', 'min:1'],
            'qty'    => ['required', 'integer', 'min:1'],
            // 'status' => ['sometime', 'boolean'],
            // 'image'  => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // $imagePath = null;

            // if ($request->hasFile('image')) {
            //     $imagePath = $request->file('image')->store('products', 'public');
            // }

            $product = Product::create([
                'name'   => $request->name,
                'price'  => $request->price,
                'qty'    => $request->qty,
                // 'status' => $request->status,
                // 'image'  => $imagePath,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Product created successfully',
                'data'    => $product,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error'  => $th->getMessage(),
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
                    'status'  => 'error',
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'status'  => "success",
                "product" => $product,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error'  => $th->getMessage(),
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
                    'name'   => ['sometimes', 'string', 'max:255'],
                    'price'  => ['sometimes', 'numeric', 'min:1'],
                    'qty'    => ['sometimes', 'integer', 'min:1'],
                    'status' => ['sometimes', 'in:1,0'],
                ]);


                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $product->update([
                    'name'   => $request->name,
                    'price'  => $request->price,
                    'qty'    => $request->qty,
                    'status' => $request->status,
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Product updated successfully',
                    'product' => $product,
                ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 'error',
                    'error'  => $th->getMessage(),
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
                        "status"  => "error",
                        "message" => "Product Not found",
                    ], 404);
                }

                $product->delete();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Product deleted successfully',
                ], 200);

            }
        }
