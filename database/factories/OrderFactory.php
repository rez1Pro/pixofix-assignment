<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'processing', 'in-progress', 'completed', 'cancelled'];
        $status = $this->faker->randomElement($statuses);

        $completedAt = null;
        $approvedAt = null;

        if ($status === 'completed') {
            $completedAt = now()->subDays(rand(1, 10));
            // 50% chance the completed order is also approved
            if ($this->faker->boolean(50)) {
                $approvedAt = $completedAt->copy()->addDays(rand(1, 3));
            }
        }

        return [
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('##??##')),
            'name' => $this->faker->catchPhrase(),
            'description' => $this->faker->paragraph(3),
            'created_by' => User::factory(),
            'customer_name' => $this->faker->company(),
            'status' => $status,
            'deadline' => now()->addDays(rand(5, 30)),
            'completed_at' => $completedAt,
            'approved_at' => $approvedAt,
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'completed_at' => null,
                'approved_at' => null,
            ];
        });
    }

    /**
     * Indicate that the order is in progress.
     */
    public function inProgress(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in-progress',
                'completed_at' => null,
                'approved_at' => null,
            ];
        });
    }

    /**
     * Indicate that the order is in processing.
     */
    public function processing(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'completed_at' => null,
                'approved_at' => null,
            ];
        });
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_at' => now()->subDays(rand(1, 5)),
                'approved_at' => null,
            ];
        });
    }

    /**
     * Indicate that the order is approved.
     */
    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            $completedAt = now()->subDays(rand(2, 10));

            return [
                'status' => 'completed',
                'completed_at' => $completedAt,
                'approved_at' => $completedAt->copy()->addDays(rand(1, 3)),
            ];
        });
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'completed_at' => null,
                'approved_at' => null,
            ];
        });
    }
}