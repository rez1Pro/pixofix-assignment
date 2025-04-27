<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileClaim>
 */
class FileClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();
        $user = User::factory()->create();

        // Create some file IDs (these won't actually exist in the database)
        $fileIds = [];
        $fileCount = $this->faker->numberBetween(1, 10);
        for ($i = 1; $i <= $fileCount; $i++) {
            $fileIds[] = $this->faker->numberBetween(1, 1000);
        }

        $isCompleted = $this->faker->boolean(30); // 30% chance the claim is completed
        $completedAt = $isCompleted ? now()->subDays(rand(1, 5)) : null;

        return [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'file_ids' => $fileIds,
            'claimed_at' => now()->subDays(rand(1, 10)),
            'completed_at' => $completedAt,
            'is_completed' => $isCompleted,
        ];
    }

    /**
     * Indicate that the file claim is completed.
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_completed' => true,
                'completed_at' => now()->subDays(rand(1, 5)),
            ];
        });
    }

    /**
     * Indicate that the file claim is not completed.
     */
    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_completed' => false,
                'completed_at' => null,
            ];
        });
    }
}