<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index()
    {
        return ProductCollection::make(
            Product::with('images')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'images'      => 'required|array|size:4',
            'images.*'    => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $product = Product::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price'       => $validated['price'],
            'user_id'     => auth()->id(),
        ]);

        foreach ($request->file('images') as $imageFile) {
            $path = $imageFile->store('products', 'public');
            $product->images()->create([
                'url' => asset('storage/' . $path),
            ]);
        }

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => new ProductResource($product->load('images')),
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json(new ProductResource($product));
    }

    public function update(Request $request, Product $product)
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'price'              => 'required|numeric',
            'images'             => 'nullable|array',
            'images.*'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'deletedImageUrls'   => 'nullable|array',
            'deletedImageUrls.*' => 'nullable|string',
        ]);

        // Actualiza datos base
        $product->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price'       => $validated['price'],
        ]);

        // 1) Eliminar solo las imágenes marcadas
        if ($request->has('deletedImageUrls')) {
            foreach ($request->deletedImageUrls as $url) {
                $image = $product->images()->where('url', $url)->first();
                if ($image) {
                    // asset('storage/xxx') -> quitar el prefix para borrar del disco 'public'
                    $relative = str_replace(asset('storage') . '/', '', $image->url);
                    if (Storage::disk('public')->exists($relative)) {
                        Storage::disk('public')->delete($relative);
                    }
                    $image->delete();
                }
            }
        }

        // 2) Agregar nuevas imágenes
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'url' => asset('storage/' . $path),
                ]);
            }
        }

        // 3) (Opcional) validar límite de 4 total
        $total = $product->images()->count();
        if ($total > 4) {
            return response()->json([
                'message' => 'Máximo 4 imágenes por producto',
            ], 422);
        }

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'product' => new ProductResource($product->load('images')),
        ]);
    }

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        foreach ($product->images as $image) {
            $relative = str_replace(asset('storage') . '/', '', $image->url);
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            $image->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado exitosamente'], 200);
    }
}
