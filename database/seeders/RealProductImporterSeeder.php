<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Seeder para importar productos reales desde archivo CSV.
 * Utilizado para solucionar el "Cold Start" de la base de datos
 * con productos reales de la comunidad Reps.
 */
class RealProductImporterSeeder extends Seeder
{
    /**
     * Ejecutar el seeder.
     * Lee el archivo CSV y crea productos con sus imágenes.
     */
    public function run(): void
    {
        // Ruta del archivo CSV
        $csvPath = database_path('data/products_seed.csv');

        // Verificar que el archivo existe
        if (!file_exists($csvPath)) {
            $this->command->error("El archivo CSV no existe: {$csvPath}");
            return;
        }

        // Abrir el archivo CSV
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            $this->command->error("No se pudo abrir el archivo CSV");
            return;
        }

        // Saltar la cabecera
        fgetcsv($handle);

        $importedCount = 0;
        $errors = [];

        // Procesar cada línea
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Validar que tiene todas las columnas necesarias
                if (count($data) < 5) {
                    $errors[] = "Línea con datos insuficientes: " . implode(',', $data);
                    continue;
                }

                // Extraer datos del CSV
                $name = trim($data[0]);
                $brand = trim($data[1]);
                $sourceLink = trim($data[2]);
                $imageUrl = trim($data[3]);
                $categoryName = trim($data[4]);

                // 1. Buscar o crear la categoría
                $category = $this->findOrCreateCategory($categoryName);

                // 2. Extraer el source_id del enlace original
                $sourceId = $this->extractSourceId($sourceLink);

                // 3. Determinar el marketplace
                $marketplace = $this->detectMarketplace($sourceLink);

                // 4. Crear el producto
                $product = Product::create([
                    'name' => $name,
                    'brand' => $brand,
                    'source_id' => $sourceId,
                    'marketplace' => $marketplace,
                    'original_link' => $sourceLink,
                    'category_id' => $category?->id,
                ]);

                // 5. Crear la imagen del producto
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'type' => 'original',
                ]);

                $importedCount++;
                $this->command->info("✓ Importado: {$name} ({$brand})");

            } catch (\Exception $e) {
                $errors[] = "Error procesando producto: " . ($data[0] ?? 'desconocido') . " - " . $e->getMessage();
                Log::error("RealProductImporterSeeder error", [
                    'data' => $data,
                    'error' => $e->getMessage()
                ]);
            }
        }

        fclose($handle);

        // Resumen final
        $this->command->newLine();
        $this->command->info("=== RESUMEN DE IMPORTACIÓN ===");
        $this->command->info("Productos importados: {$importedCount}");

        if (count($errors) > 0) {
            $this->command->warn("Errores encontrados: " . count($errors));
            foreach ($errors as $error) {
                $this->command->warn("  - {$error}");
            }
        }
    }

    /**
     * Buscar o crear una categoría por nombre.
     * 
     * @param string $name Nombre de la categoría
     * @return Category|null
     */
    private function findOrCreateCategory(string $name): ?Category
    {
        if (empty($name)) {
            return null;
        }

        return Category::firstOrCreate(
            ['name' => $name],
            ['description' => "Categoría: {$name}"]
        );
    }

    /**
     * Extraer el source_id del enlace original usando regex.
     * 
     * - Weidian: itemID=XXXXXX
     * - Taobao: id=XXXXXX
     * - 1688: offer/XXXXXX.html
     * 
     * @param string $link Enlace completo del producto
     * @return string ID extraído o 'unknown'
     */
    private function extractSourceId(string $link): string
    {
        // Weidian: itemID=4477393523
        if (preg_match('/itemID=(\d+)/i', $link, $matches)) {
            return $matches[1];
        }

        // Taobao: id=6923485671
        if (preg_match('/id=(\d+)/i', $link, $matches)) {
            return $matches[1];
        }

        // 1688: offer/6753247890.html
        if (preg_match('/offer\/(\d+)\.html/i', $link, $matches)) {
            return $matches[1];
        }

        // Si no se puede extraer, generar un ID único
        return 'unknown_' . time() . '_' . rand(1000, 9999);
    }

    /**
     * Detectar el marketplace según el dominio del enlace.
     * 
     * @param string $link Enlace completo del producto
     * @return string 'taobao', 'weidian' o '1688'
     */
    private function detectMarketplace(string $link): string
    {
        if (str_contains($link, 'weidian.com')) {
            return 'weidian';
        }

        if (str_contains($link, 'taobao.com')) {
            return 'taobao';
        }

        if (str_contains($link, '1688.com')) {
            return '1688';
        }

        // Por defecto, taobao
        return 'taobao';
    }
}
