<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\Outfit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutfitModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_outfit_belongs_to_user()
    {
        $user = User::factory()->create();
        $outfit = Outfit::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $outfit->user);
    }

    public function test_outfit_has_pivot_data_with_products()
    {
        $outfit = Outfit::factory()->create();
        $product = Product::factory()->create();

        $outfit->products()->attach($product->id, [
            'pos_x' => 10.5,
            'pos_y' => 20.5,
            'rotation' => 0,
            'scale_x' => 1,
            'scale_y' => 1,
            'z_index' => 1,
            'is_flipped' => false
        ]);

        $this->assertEquals(10.5, $outfit->products()->first()->pivot->pos_x);
    }
}
