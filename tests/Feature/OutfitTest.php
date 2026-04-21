<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Outfit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_outfit()
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(2)->create();

        $outfitData = [
            'title' => 'Cool Style',
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'x' => 100,
                    'y' => 100,
                    'rotation' => 0,
                    'scaleX' => 1,
                    'scaleY' => 1,
                    'zIndex' => 1,
                    'isFlipped' => false
                ],
                [
                    'product_id' => $products[1]->id,
                    'x' => 200,
                    'y' => 200,
                    'rotation' => 10,
                    'scaleX' => 1.2,
                    'scaleY' => 1.2,
                    'zIndex' => 2,
                    'isFlipped' => true
                ]
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/outfits', $outfitData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('outfits', ['title' => 'Cool Style']);
    }

    public function test_user_can_delete_own_outfit()
    {
        $user = User::factory()->create();
        $outfit = Outfit::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/outfits/{$outfit->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('outfits', ['id' => $outfit->id]);
    }
}
