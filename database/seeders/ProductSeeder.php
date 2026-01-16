<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Limpiar tablas relacionadas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        ProductImage::truncate();
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🧹 Tablas limpiadas. Iniciando importación masiva...');

        // 2. Crear Categorías Base
        // Mapeamos el nombre del CSV al ID de la base de datos
        $categoriesMap = [
            'Sneakers' => 'Calzado deportivo, Jordans, Yeezy...',
            'Hoodies' => 'Sudaderas con capucha y sweaters',
            'T-Shirts' => 'Camisetas gráficas y básicas',
            'Pants' => 'Cargo pants, Jeans, Sweatpants',
            'Accessories' => 'Gorras, Bolsos, Joyería'
        ];

        $catIds = [];
        foreach ($categoriesMap as $name => $desc) {
            $cat = Category::create(['name' => $name, 'description' => $desc]);
            $catIds[$name] = $cat->id;
        }

        // 3. Leer CSV
        $csvPath = database_path('seeders/data/products_seed.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("❌ No se encontró el archivo: $csvPath");
            return;
        }

        $file = fopen($csvPath, 'r');
        $header = fgetcsv($file); // Saltar cabecera: name,brand,category,marketplace,source_id,original_link,image_url

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            // Mapeo de columnas por índice basado en el orden del CSV
            $name = $row[0];
            $brand = $row[1];
            $catName = $row[2];
            $marketplace = $row[3];
            $sourceId = $row[4];
            $originalLink = $row[5];
            $imageUrl = $row[6];

            // Obtener ID de categoría (fallback a Sneakers si no existe)
            $categoryId = $catIds[$catName] ?? $catIds['Sneakers'];

            // Crear Producto
            $product = Product::create([
                'name' => $name,
                'brand' => $brand,
                'category_id' => $categoryId,
                'marketplace' => $marketplace,
                'source_id' => $sourceId,
                'original_link' => $originalLink,
            ]);

            // Crear Imagen "Original"
            ProductImage::create([
                'product_id' => $product->id,
                'url' => $imageUrl,
                'type' => 'original'
            ]);

            // Crear Imagen "QC" (Simluada duplicando la original para tener datos en las tabs)
            // En producción, esto vendría del scraping real
            ProductImage::create([
                'product_id' => $product->id,
                'url' => $imageUrl, // Usamos la misma por ahora
                'type' => 'qc'
            ]);

            $count++;
        }

        fclose($file);

        $this->command->info("✅ Importación completada: $count productos insertados desde CSV.");
    }
}
