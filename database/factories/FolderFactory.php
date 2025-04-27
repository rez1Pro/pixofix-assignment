<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Folder>
 */
class FolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $folderNames = [
            'Original Files',
            'Edited Files',
            'Final Delivery',
            'Customer Feedback',
            'Screenshots',
            'Reference Images',
            'Templates',
            'Raw Files',
            'Documentation',
            'Proofs',
            'Samples',
            'Artwork',
        ];

        // Add a random number suffix to ensure uniqueness
        $name = $this->faker->randomElement($folderNames) . ' ' . $this->faker->unique()->numberBetween(1, 999);

        return [
            'order_id' => Order::factory(),
            'name' => $name,
            'is_open' => $this->faker->boolean(80), // 80% chance to be open by default
        ];
    }

    /**
     * Indicate that the folder is open.
     */
    public function open(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_open' => true,
            ];
        });
    }

    /**
     * Indicate that the folder is closed.
     */
    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_open' => false,
            ];
        });
    }
}