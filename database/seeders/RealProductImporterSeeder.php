<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Source;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Seeder actualizado para importar productos reales usando la nueva estructura relacional.
 */
class RealProductImporterSeeder extends Seeder
{
    /**
     * Ejecutar el seeder.
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
                if (count($data) < 5) {
                    $errors[] = "Línea con datos insuficientes: " . implode(',', $data);
                    continue;
                }

                // Extraer datos del CSV
                $name = trim($data[0]);
                $brandName = trim($data[1]);
                $sourceLink = trim($data[2]);
                $imageUrl = trim($data[3]);
                $categoryName = trim($data[4]);

                // 1. Buscar o crear la categoría
                $category = $this->findOrCreateCategory($categoryName);

                // 2. Buscar o crear la MARCA (Nueva estructura)
                $brand = $this->findOrCreateBrand($brandName);

                // 3. Detectar y buscar/crear el MARKETPLACE (Source) (Nueva estructura)
                $source = $this->detectAndGetSource($sourceLink);

                // 4. Extraer el external_id (antiguo source_id)
                $externalId = $this->extractExternalId($sourceLink);

                // 5. Crear el producto usando la nueva estructura de FKs
                $product = Product::create([
                    'name' => $name,
                    'brand_id' => $brand?->id,
                    'source_id' => $source?->id,
                    'external_id' => $externalId,
                    'original_link' => $sourceLink,
                    'category_id' => $category?->id,
                ]);

                // 6. Crear la imagen del producto
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'type' => 'original',
                ]);

                $importedCount++;
                $this->command->info("✓ Importado: {$name} (Marca: {$brandName} | Source: {$source->name})");

            } catch (\Exception $e) {
                $errors[] = "Error procesando producto: " . ($data[0] ?? 'desconocido') . " - " . $e->getMessage();
                Log::error("RealProductImporterSeeder error", [
                    'data' => $data,
                    'error' => $e->getMessage()
                ]);
            }
        }

        fclose($handle);

        $this->command->newLine();
        $this->command->info("=== RESUMEN DE IMPORTACIÓN (NUEVA ESTRUCTURA) ===");
        $this->command->info("Productos importados: {$importedCount}");

        if (count($errors) > 0) {
            $this->command->warn("Errores encontrados: " . count($errors));
        }
    }

    private function findOrCreateCategory(string $name): ?Category
    {
        if (empty($name)) return null;
        return Category::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'description' => "Categoría: {$name}"]
        );
    }

    private function findOrCreateBrand(string $name): ?Brand
    {
        if (empty($name)) return null;
        return Brand::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }

    private function detectAndGetSource(string $link): Source
    {
        $name = 'Taobao';
        $slug = 'taobao';
        $baseUrl = 'https://taobao.com';

        if (str_contains($link, 'weidian.com')) {
            $name = 'Weidian';
            $slug = 'weidian';
            $baseUrl = 'https://weidian.com';
        } elseif (str_contains($link, '1688.com')) {
            $name = '1688';
            $slug = '1688';
            $baseUrl = 'https://1688.com';
        }

        return Source::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'base_url' => $baseUrl]
        );
    }

    private function extractExternalId(string $link): string
    {
        if (preg_match('/itemID=(\d+)/i', $link, $matches)) return $matches[1];
        if (preg_match('/id=(\d+)/i', $link, $matches)) return $matches[1];
        if (preg_match('/offer\/(\d+)\.html/i', $link, $matches)) return $matches[1];
        return 'ext_' . time() . '_' . rand(1000, 9999);
    }
}
