<?php

namespace Database\Factories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subfolder>
 */
class SubfolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subfolderNames = [
            'Version 1',
            'Version 2',
            'Drafts',
            'Proofs',
            'Samples',
            'Revisions',
            'Client Notes',
            'Final',
            'Archive',
            'Working Files',
        ];

        // Add a random suffix to ensure uniqueness
        $name = $this->faker->randomElement($subfolderNames) . ' ' . $this->faker->unique()->numberBetween(1, 999);

        return [
            'folder_id' => Folder::factory(),
            'name' => $name,
            'is_open' => $this->faker->boolean(80), // 80% chance to be open by default
        ];
    }

    /**
     * Indicate that the subfolder is open.
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
     * Indicate that the subfolder is closed.
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