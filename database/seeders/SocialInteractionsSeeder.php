<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;

class SocialInteractionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        if ($users->count() < 2 || $products->count() < 5) {
            return;
        }

        // Para cada usuario, asignar aleatoriamente algunos favoritos y seguidores
        foreach ($users as $user) {
            // Asignar favoritos (entre 2 y 8 productos)
            $randomProducts = $products->random(rand(2, 8))->pluck('id');
            $user->favorites()->syncWithoutDetaching($randomProducts);

            // Asignar seguidores (el usuario sigue a otros usuarios aleatoriamente)
            $otherUsers = $users->where('id', '!=', $user->id);
            if ($otherUsers->count() > 0) {
                // Sigue a entre 1 y el total de usuarios
                $followingCount = rand(1, min(3, $otherUsers->count()));
                $randomFollowing = $otherUsers->random($followingCount)->pluck('id');
                $user->following()->syncWithoutDetaching($randomFollowing);
            }
        }
        
        $this->command->info('✅ Interacciones sociales (followers y favoritos) creadas correctamente.');
    }
}
