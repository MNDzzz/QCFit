<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'external_id' => $this->faker->unique()->randomNumber(8),
            'original_link' => $this->faker->url,
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'source_id' => Source::factory(),
        ];
    }
}
