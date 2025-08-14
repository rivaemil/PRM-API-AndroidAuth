<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Usuarios "protegidos" (dueños de los productos seed)
        $ownerA = User::create([
            'name' => 'Owner A',
            'email' => 'ownerA@example.com',
            'password' => Hash::make('password123'),
        ]);

        $ownerB = User::create([
            'name' => 'Owner B',
            'email' => 'ownerB@example.com',
            'password' => Hash::make('password123'),
        ]);

        // (Opcional) agrega una imagen (morphOne/morphMany) a cada usuario, como en tu seeder previo
        Image::factory()->create([
            'imageable_id'   => $ownerA->id,
            'imageable_type' => \App\Models\User::class,
        ]);

        Image::factory()->create([
            'imageable_id'   => $ownerB->id,
            'imageable_type' => \App\Models\User::class,
        ]);

        // 2) 10 productos totales (como en tu seeder previo), pero ahora SIEMPRE con user_id asignado
        //    Repartimos 5 y 5 entre Owner A y Owner B
        $productsA = Product::factory(5)->create([
            'user_id' => $ownerA->id,
        ]);

        $productsB = Product::factory(5)->create([
            'user_id' => $ownerB->id,
        ]);

        // 3) 4 imágenes por producto (respeta tu seeder original)
        $productsA->each(function (Product $product) {
            Image::factory(4)->create([
                'imageable_id'   => $product->id,
                'imageable_type' => \App\Models\Product::class,
            ]);
        });

        $productsB->each(function (Product $product) {
            Image::factory(4)->create([
                'imageable_id'   => $product->id,
                'imageable_type' => \App\Models\Product::class,
            ]);
        });

        /**
         * Nota para pruebas:
         * - Crea luego un usuario "tercero" desde tu endpoint /api/register o /api/login
         *   y usa su token en Postman.
         * - Ese usuario NO podrá actualizar/eliminar los productos de Owner A/B
         *   (403 por policy), demostrando el acceso restringido.
         */
    }
}
