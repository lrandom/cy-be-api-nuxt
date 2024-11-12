<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->text(100),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'description' => $this->faker->text,
            'preview_img_path' => $this->faker->imageUrl(),
            'stock' => $this->faker->numberBetween(1, 100),
            'category_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
