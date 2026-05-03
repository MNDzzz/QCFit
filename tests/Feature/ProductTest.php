<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Permission::findOrCreate('product-create', 'web');
        Permission::findOrCreate('product-edit', 'web');
        Permission::findOrCreate('product-delete', 'web');
        
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(['product-create', 'product-edit', 'product-delete']);
    }

    public function test_public_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_public_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_admin_can_create_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $source = Source::factory()->create();

        $productData = [
            'name' => 'New Test Product',
            'external_id' => 'EXT123',
            'original_link' => 'https://example.com',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'source_id' => $source->id,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'New Test Product']);
    }

    public function test_non_admin_cannot_create_product()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Illegal Product',
            'external_id' => 'EXT_ILLEGAL',
            'original_link' => 'https://example.com/illegal',
            'category_id' => $category->id,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/products', $productData);

        $response->assertStatus(403);
    }
}
