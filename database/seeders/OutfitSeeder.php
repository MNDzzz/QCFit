<?php

namespace Database\Seeders;

use App\Models\Outfit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * OutfitSeeder
 * 
 * Crea outfits de prueba para testear la funcionalidad Remix.
 * Los outfits incluyen productos con sus posiciones en el canvas.
 */
class OutfitSeeder extends Seeder
{
    /**
     * Ejecutar el seeder de outfits.
     */
    public function run(): void
    {
        // Obtener el primer usuario o crear uno de prueba
        $user = User::first();

        if (!$user) {
            $this->command->warn('No hay usuarios. Crea un usuario primero.');
            return;
        }

        // Obtener productos existentes
        $products = Product::with('images')->take(5)->get();

        if ($products->count() < 2) {
            $this->command->warn('Se necesitan al menos 2 productos con imágenes para crear outfits.');
            return;
        }

        // Crear outfit 1: Street Style
        $outfit1 = Outfit::create([
            'user_id' => $user->id,
            'title' => 'Street Style Summer 2024',
            'description' => 'Un look casual perfecto para el verano con piezas de alta gama.',
            'thumbnail_url' => null,
        ]);

        // Añadir productos con posiciones
        $this->attachProductsToOutfit($outfit1, $products->take(3));

        $this->command->info("✅ Outfit '{$outfit1->title}' creado con ID: {$outfit1->id}");

        // Crear outfit 2: Minimal Look
        $outfit2 = Outfit::create([
            'user_id' => $user->id,
            'title' => 'Minimal Essentials',
            'description' => 'Menos es más. Un outfit minimalista con piezas atemporales.',
            'thumbnail_url' => null,
        ]);

        $this->attachProductsToOutfit($outfit2, $products->take(2));

        $this->command->info("✅ Outfit '{$outfit2->title}' creado con ID: {$outfit2->id}");

        // Crear outfit 3: Bold Statement
        if ($products->count() >= 4) {
            $outfit3 = Outfit::create([
                'user_id' => $user->id,
                'title' => 'Bold Statement',
                'description' => 'Atrévete a destacar con este look audaz.',
                'thumbnail_url' => null,
            ]);

            $this->attachProductsToOutfit($outfit3, $products->slice(1, 4));

            $this->command->info("✅ Outfit '{$outfit3->title}' creado con ID: {$outfit3->id}");
        }

        $this->command->info("\n🎉 Outfits de prueba creados. Usa ?outfit_id=X para probar Remix.");
    }

    /**
     * Adjuntar productos a un outfit con posiciones de canvas.
     */
    private function attachProductsToOutfit(Outfit $outfit, $products): void
    {
        $baseX = 150;
        $baseY = 100;
        $index = 0;

        foreach ($products as $product) {
            // Obtener una imagen del producto o null
            $imageId = $product->images->first()?->id;

            $outfit->products()->attach($product->id, [
                'pos_x' => $baseX + ($index * 220),
                'pos_y' => $baseY + ($index * 30),
                'rotation' => rand(-15, 15), // Rotación aleatoria leve
                'scale_x' => 1,
                'scale_y' => 1,
                'z_index' => $index + 1,
                'is_flipped' => false,
                'selected_image_id' => $imageId,
            ]);

            $index++;
        }
    }
}
