<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * LocalProductSeeder
 *
 * Creates products from local webp images in public/images/products/.
 * This is the single source of truth for the product catalog.
 * Each product has real QC photos (Front, Back, Logo, Size, etc.)
 * and one Original reference image.
 */
class LocalProductSeeder extends Seeder
{
    /**
     * Mapping of image base names to proper product info.
     * Key = base name derived from filenames (e.g. "AmiParis-Sweater")
     * Values = human-readable name, brand, and category.
     */
    private array $productMap = [
        'AmiParis-Sweater' => [
            'name' => 'Ami Paris Heart Sweater',
            'brand' => 'Ami Paris',
            'category' => 'Sweaters',
        ],
        'AmiParis-TShirt' => [
            'name' => 'Ami Paris Heart T-Shirt',
            'brand' => 'Ami Paris',
            'category' => 'T-Shirts',
        ],
        'Arcteryx-Jacket' => [
            'name' => "Arc'teryx Alpha SV Jacket",
            'brand' => "Arc'teryx",
            'category' => 'Jackets',
        ],
        'Balenciaga-Hoodie' => [
            'name' => 'Balenciaga Logo Hoodie',
            'brand' => 'Balenciaga',
            'category' => 'Hoodies',
        ],
        'Canada-Goose-Vest' => [
            'name' => 'Canada Goose Freestyle Vest',
            'brand' => 'Canada Goose',
            'category' => 'Jackets',
        ],
        'Carhartt-Sweater' => [
            'name' => 'Carhartt WIP Sweater',
            'brand' => 'Carhartt',
            'category' => 'Sweaters',
        ],
        'EssentialsOfGod-Hoodie' => [
            'name' => 'Fear of God Essentials Hoodie',
            'brand' => 'Fear of God',
            'category' => 'Hoodies',
        ],
        'GalleryDept-Flared-Pants' => [
            'name' => 'Gallery Dept Flared Pants',
            'brand' => 'Gallery Dept',
            'category' => 'Pants',
        ],
        'Goyard-Card-Holder' => [
            'name' => 'Goyard Saint Sulpice Card Holder',
            'brand' => 'Goyard',
            'category' => 'Accessories',
        ],
        'Gucci-Hat' => [
            'name' => 'Gucci GG Canvas Hat',
            'brand' => 'Gucci',
            'category' => 'Accessories',
        ],
        'Jordan-Travis-Scott' => [
            'name' => 'Jordan 1 Low x Travis Scott',
            'brand' => 'Nike',
            'category' => 'Sneakers',
        ],
        'Lacoste-Bracelet' => [
            'name' => 'Lacoste Crocodile Bracelet',
            'brand' => 'Lacoste',
            'category' => 'Accessories',
        ],
        'LV-Belt' => [
            'name' => 'Louis Vuitton Initiales Belt',
            'brand' => 'Louis Vuitton',
            'category' => 'Accessories',
        ],
        'LV-Cap' => [
            'name' => 'Louis Vuitton Monogram Cap',
            'brand' => 'Louis Vuitton',
            'category' => 'Accessories',
        ],
        'LV-Skates' => [
            'name' => 'Louis Vuitton Skate Sneakers',
            'brand' => 'Louis Vuitton',
            'category' => 'Sneakers',
        ],
        'LV-Sweater' => [
            'name' => 'Louis Vuitton Monogram Sweater',
            'brand' => 'Louis Vuitton',
            'category' => 'Sweaters',
        ],
        'Maison-Black' => [
            'name' => 'Maison Margiela Replica Black',
            'brand' => 'Maison Margiela',
            'category' => 'Sneakers',
        ],
        'Maison-White' => [
            'name' => 'Maison Margiela Replica White',
            'brand' => 'Maison Margiela',
            'category' => 'Sneakers',
        ],
        'Nike-AF' => [
            'name' => 'Nike Air Force 1',
            'brand' => 'Nike',
            'category' => 'Sneakers',
        ],
        'Nike-FCB-Jersey' => [
            'name' => 'Nike FC Barcelona Jersey',
            'brand' => 'Nike',
            'category' => 'T-Shirts',
        ],
        'Nike-SB' => [
            'name' => 'Nike SB Dunk Low',
            'brand' => 'Nike',
            'category' => 'Sneakers',
        ],
        'Prada-Cups' => [
            'name' => 'Prada Cup Sneakers',
            'brand' => 'Prada',
            'category' => 'Sneakers',
        ],
        'RalphLaurent-TShirt' => [
            'name' => 'Ralph Lauren Polo T-Shirt',
            'brand' => 'Ralph Lauren',
            'category' => 'T-Shirts',
        ],
        'RickOwens-Shoes' => [
            'name' => 'Rick Owens Ramones',
            'brand' => 'Rick Owens',
            'category' => 'Sneakers',
        ],
        'Stussy-Hoodie' => [
            'name' => 'Stussy 8 Ball Hoodie',
            'brand' => 'Stussy',
            'category' => 'Hoodies',
        ],
        'Stussy-Wallet' => [
            'name' => 'Stussy Leather Wallet',
            'brand' => 'Stussy',
            'category' => 'Accessories',
        ],
        'Supreme-Hoodie' => [
            'name' => 'Supreme Box Logo Hoodie',
            'brand' => 'Supreme',
            'category' => 'Hoodies',
        ],
        'Supreme-Jogger' => [
            'name' => 'Supreme Jogger Pants',
            'brand' => 'Supreme',
            'category' => 'Pants',
        ],
        'TrueReligion-Jeans' => [
            'name' => 'True Religion Ricky Jeans',
            'brand' => 'True Religion',
            'category' => 'Pants',
        ],
        'UnderArmour-TShirt' => [
            'name' => 'Under Armour Tech T-Shirt',
            'brand' => 'Under Armour',
            'category' => 'T-Shirts',
        ],
        'Yeezy-Hoodie' => [
            'name' => 'Yeezy Gap Hoodie',
            'brand' => 'Yeezy',
            'category' => 'Hoodies',
        ],
    ];

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $directory = public_path('images/products');

        if (!File::exists($directory)) {
            $this->command->error("Directory does not exist: {$directory}");
            return;
        }

        $files = File::files($directory);
        $this->command->info("Found " . count($files) . " image files.");

        // Group files by product base name
        $productsMap = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

            // Format: Brand-ProductName-Suffix (e.g. Balenciaga-Hoodie-Front)
            $parts = explode('-', $nameWithoutExt);

            // Last part is the suffix (Original, Front, Back, Size, Logo, etc.)
            $suffix = array_pop($parts);

            // The rest is the product base name
            $baseName = implode('-', $parts);

            if (!isset($productsMap[$baseName])) {
                $productsMap[$baseName] = [];
            }

            // Determine image type: 'original' for reference photo, 'qc' for QC photos
            $type = strtolower($suffix) === 'original' ? 'original' : 'qc';

            $productsMap[$baseName][] = [
                'filename' => $filename,
                'type' => $type,
            ];
        }

        $imported = 0;

        foreach ($productsMap as $baseName => $images) {
            // Get product info from mapping, or generate fallback
            $info = $this->productMap[$baseName] ?? [
                'name' => str_replace('-', ' ', $baseName),
                'brand' => explode('-', $baseName)[0] ?? 'Unknown',
                'category' => 'Clothing',
            ];

            // Find or create brand
            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($info['brand'])],
                ['name' => $info['brand']]
            );

            // Find or create category
            $category = Category::firstOrCreate(
                ['name' => $info['category']],
                ['description' => $info['category'] . ' category']
            );

            // Create product
            $product = Product::create([
                'name' => $info['name'],
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'external_id' => 'local_' . Str::slug($baseName),
                'original_link' => '#',
            ]);

            // Create product images
            foreach ($images as $imgData) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => '/images/products/' . $imgData['filename'],
                    'type' => $imgData['type'],
                ]);
            }

            $imported++;
            $this->command->info("✓ {$info['name']} ({$brand->name} | {$category->name}) — " . count($images) . " images");
        }

        $this->command->newLine();
        $this->command->info("=== IMPORT COMPLETE ===");
        $this->command->info("Products created: {$imported}");
        $this->command->info("Total images linked: " . ProductImage::count());
    }
}
