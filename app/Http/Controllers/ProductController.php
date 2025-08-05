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
        \Log::info("Iniciando actualización del producto ID: {$product->id}");

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deletedImageUrls' => 'nullable|array',
            'deletedImageUrls.*' => 'string',
        ]);

        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
        ]);

        // Eliminar imágenes seleccionadas por el usuario
        if ($request->has('deletedImageUrls')) {
            foreach ($request->deletedImageUrls as $url) {
                $image = $product->images()->where('url', $url)->first();
                if ($image) {
                    $path = str_replace('/storage/', '', $image->url);
                    Storage::disk('public')->delete($path);
                    $image->delete();
                    \Log::info("Imagen eliminada: {$url}");
                }
            }
        }

        // Verificar cuántas imágenes existen ahora
        $currentImageCount = $product->images()->count();

        // Agregar nuevas imágenes si no se supera el límite de 4
        if ($request->hasFile('images')) {
            $newImages = $request->file('images');
            $totalAfterUpload = $currentImageCount + count($newImages);

            if ($totalAfterUpload > 4) {
                return response()->json([
                    'message' => 'Solo se permiten hasta 4 imágenes por producto.',
                ], 422);
            }

            foreach ($newImages as $imageFile) {
                $path = $imageFile->store('products', 'public');
                $product->images()->create([
                    'url' => Storage::url($path),
                ]);
                \Log::info("Imagen agregada: " . Storage::url($path));
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
        //
    }
}
