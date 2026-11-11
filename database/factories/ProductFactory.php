<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $buy = fake()->numberBetween(2, 80) * 1000;

        return [
            'category_id' => null,
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'barcode' => fake()->unique()->numerify('899##########'),
            'name' => fake()->unique()->words(3, true),
            'brand' => fake()->company(),
            'purchase_price' => $buy,
            'selling_price' => $buy + (int) round($buy * 0.25, -2),
            'stock' => fake()->numberBetween(5, 60),
            'min_stock' => 5,
            'is_active' => true,
        ];
    }
}
