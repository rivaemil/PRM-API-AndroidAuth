<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Product;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = Product::factory(15)->create();

        $products->each(function ($product) {
            Image::factory()->create([
                'imageable_id' => $product->id,
                'imageable_type' => \App\Models\Product::class,
            ]);
        });

        $users = User::factory(15)->create();

        $users->each(function ($user) {
            Image::factory()->create([
                'imageable_id' => $user->id,
                'imageable_type' => \App\Models\User::class,
            ]);
        });
    }
}
