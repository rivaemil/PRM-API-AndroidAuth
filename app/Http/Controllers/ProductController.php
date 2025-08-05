<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductCollection::make(
            Product::with('images')->get()
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'images'      => 'required|array|size:4',
            'images.*'    => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            ]);

        foreach ($request->file('images') as $imageFile) {
            $imagePath = $imageFile->store('products', 'public');
            $product->images()->create([
                'url' => Storage::url($imagePath),
            ]);
        }

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => new ProductResource($product->load('images')),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'images'      => 'nullable|array|size:4',
            'images.*'    => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        if ($request->hasFile('images')) {
            foreach ($product->images as $image) {
                $path = str_replace('/storage', '', $image->url); // porque Storage::url agrega /storage
                Storage::disk('public')->delete($path);
                $image->delete();
            }

            foreach ($request->file('images') as $imageFile) {
                $imagePath = $imageFile->store('products', 'public');
                $product->images()->create([
                    'url' => Storage::url($imagePath),
                ]);
            }
        }

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'product' => new ProductResource($product->load('images')),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Eliminar imágenes relacionadas
        foreach ($product->images as $image) {
            $path = str_replace('/storage', '', $image->url);
            Storage::disk('public')->delete($path);
            $image->delete();
        }

        // Eliminar el producto
        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ], 200);
    }
}
