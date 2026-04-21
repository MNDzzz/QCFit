<?php

namespace Database\Factories;

use App\Models\Outfit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OutfitFactory extends Factory
{
    protected $model = Outfit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'thumbnail_url' => $this->faker->imageUrl(),
        ];
    }
}
