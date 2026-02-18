<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (Basic setup if not already done)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleUser = Role::firstOrCreate(['name' => 'user']);

        // 2. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@qcfit.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'alias' => 'admin_qc',
            ]
        );
        $admin->assignRole($roleAdmin);

        $user = User::firstOrCreate(
            ['email' => 'demo@qcfit.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'alias' => 'demo_styler',
            ]
        );
        $user->assignRole($roleUser);

        // 3. Categories
        $catTops = Category::create(['name' => 'Tops']);
        $catBottoms = Category::create(['name' => 'Bottoms']);
        $catShoes = Category::create(['name' => 'Shoes']);

        // 4. Products & Images

        // Product 1: Nike Dunk (Shoes)
        $p1 = Product::create([
            'category_id' => $catShoes->id,
            'name' => 'Nike Dunk Low Panda',
            'source_id' => '789123',
            'marketplace' => 'weidian',
            'brand' => 'Nike',
            'original_link' => 'https://weidian.com/item.html?itemID=123456',
        ]);

        // QC Images (Real photos)
        ProductImage::create([
            'product_id' => $p1->id,
            'url' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=600&auto=format&fit=crop', // Shoe looking down
            'type' => 'qc'
        ]);
        ProductImage::create([
            'product_id' => $p1->id,
            'url' => 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=600&auto=format&fit=crop', // Side view
            'type' => 'qc'
        ]);
        // Official Image
        ProductImage::create([
            'product_id' => $p1->id,
            'url' => 'https://static.nike.com/a/images/t_PDP_1280_v1/f_auto,q_auto:eco/510bd59a-1cce-4688-9d21-f8577543881c/dunk-low-zapatillas-87q0hf.png',
            'type' => 'original'
        ]);


        // Product 2: Stussy Tee (Tops)
        $p2 = Product::create([
            'category_id' => $catTops->id,
            'name' => 'Stussy 8 Ball Tee',
            'source_id' => '456789',
            'marketplace' => 'taobao',
            'brand' => 'Stussy',
            'original_link' => 'https://item.taobao.com/item.htm?id=987654',
        ]);

        ProductImage::create([
            'product_id' => $p2->id,
            'url' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=600&auto=format&fit=crop', // Tee shirt folded
            'type' => 'qc'
        ]);
        ProductImage::create([
            'product_id' => $p2->id,
            'url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=600&auto=format&fit=crop', // Tee shirt hanger
            'type' => 'original'
        ]);

        // Product 3: Carhartt Cargo (Bottoms)
        $p3 = Product::create([
            'category_id' => $catBottoms->id,
            'name' => 'Carhartt WIP Aviation Pant',
            'source_id' => '112233',
            'marketplace' => '1688',
            'brand' => 'Carhartt',
            'original_link' => 'https://detail.1688.com/offer/11223344.html',
        ]);

        ProductImage::create([
            'product_id' => $p3->id,
            'url' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?q=80&w=600&auto=format&fit=crop', // Cargo pants
            'type' => 'qc'
        ]);
    }
}
