<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ImportLocalImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:local-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import local images from public/images/products and link them to products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = public_path('images/products');

        if (!File::exists($directory)) {
            $this->error("Directory does not exist: {$directory}");
            return;
        }

        $files = File::files($directory);
        
        $this->info("Found " . count($files) . " files.");

        // Group files by product name
        // Example: Balenciaga-Hoodie-Original.webp -> Base: Balenciaga-Hoodie
        $productsMap = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            
            // The format is Brand-ProductName-Suffix (e.g. Balenciaga-Hoodie-Original, Nike-AF-Side)
            $parts = explode('-', $nameWithoutExt);
            
            // The last part is the suffix (Original, Front, Back, Size, Logo, etc.)
            $suffix = array_pop($parts);
            
            // The rest is the product base name
            $baseName = implode('-', $parts);
            
            // Determine brand (usually the first word)
            $brandName = $parts[0] ?? 'Unknown';
            
            // The human readable name
            $productName = str_replace('-', ' ', $baseName);

            if (!isset($productsMap[$baseName])) {
                $productsMap[$baseName] = [
                    'name' => $productName,
                    'brand' => str_replace('-', ' ', $brandName),
                    'images' => []
                ];
            }

            $type = strtolower($suffix) === 'original' ? 'original' : 'qc';
            
            $productsMap[$baseName]['images'][] = [
                'filename' => $filename,
                'type' => $type
            ];
        }

        // Category fallback
        $category = Category::firstOrCreate(
            ['name' => 'Clothing'],
            ['description' => 'General clothing category']
        );

        $imported = 0;

        foreach ($productsMap as $baseName => $data) {
            $this->info("Processing: {$data['name']}");

            // Find or create brand
            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($data['brand'])],
                ['name' => $data['brand']]
            );

            // Find or create Product
            // We search by name
            $product = Product::firstOrCreate(
                ['name' => $data['name']],
                [
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'external_id' => 'local_' . Str::random(8),
                    'original_link' => 'https://example.com/local'
                ]
            );

            // Delete old images that are local to avoid duplicates if we run this twice
            ProductImage::where('product_id', $product->id)
                ->where('url', 'LIKE', '/images/products/%')
                ->delete();

            // Link images
            foreach ($data['images'] as $imgData) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => '/images/products/' . $imgData['filename'],
                    'type' => $imgData['type'],
                ]);
            }
            
            $imported++;
        }

        $this->info("Import completed successfully. {$imported} products processed.");
    }
}
