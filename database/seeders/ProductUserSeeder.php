<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class ProductUserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 3 usuarios con contraseña 'password123'
        $users = [
            [
                'name' => 'Usuario Uno',
                'email' => 'user1@example.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'Usuario Dos',
                'email' => 'user2@example.com',
                'password' => Hash::make('password123')
            ],
            [
                'name' => 'Usuario Tres',
                'email' => 'user3@example.com',
                'password' => Hash::make('password123')
            ],
        ];

        foreach ($users as $i => $userData) {
            $user = User::create($userData);

            // Crear producto para ese usuario con user_id asignado
            Product::create([
                'name'        => 'Producto de ' . $user->name,
                'description' => 'Descripción del producto ' . ($i + 1),
                'price'       => rand(10, 100),
                'status'     => 'active',
                'user_id'     => $user->id,
            ]);
        }
    }
}
