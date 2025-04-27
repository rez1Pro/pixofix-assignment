<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Folder;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FileItem>
 */
class FileItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $fileType = $this->faker->randomElement($fileTypes);
        $extension = substr($fileType, strpos($fileType, '/') + 1);
        $extension = $extension === 'svg+xml' ? 'svg' : $extension;

        $originalName = $this->faker->words(3, true) . '.' . $extension;
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        $order = Order::factory()->create();
        $folder = null;
        $subfolder = null;

        // 70% chance to have a folder
        if ($this->faker->boolean(70)) {
            $folder = Folder::factory()->create(['order_id' => $order->id]);

            // 40% chance to have a subfolder if there's a folder
            if ($this->faker->boolean(40)) {
                $subfolder = Subfolder::factory()->create(['folder_id' => $folder->id]);
            }
        }

        $statuses = ['pending', 'claimed', 'processing', 'completed'];
        $status = $this->faker->randomElement($statuses);

        // If status is processing, assign to a user
        $assignedTo = null;
        if ($status === 'processing') {
            $assignedTo = User::factory()->create()->id;
        }

        $directory = 'orders/' . $order->id;
        if ($folder) {
            $directory .= '/' . $folder->id;
            if ($subfolder) {
                $directory .= '/' . $subfolder->id;
            }
        }

        $path = $directory . '/' . $name . '-' . time() . '.' . $extension;

        return [
            'order_id' => $order->id,
            'folder_id' => $folder ? $folder->id : null,
            'subfolder_id' => $subfolder ? $subfolder->id : null,
            'name' => $name,
            'original_name' => $originalName,
            'path' => $path,
            'file_type' => $fileType,
            'file_size' => $this->faker->numberBetween(50000, 5000000), // 50KB to 5MB
            'status' => $status,
            'assigned_to' => $assignedTo,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterMaking(function (\App\Models\FileItem $fileItem) {
            //
        })->afterCreating(function (\App\Models\FileItem $fileItem) {
            // No need to actually create the file for seeding/testing purposes
        });
    }

    /**
     * Indicate that the file is pending.
     */
    public function pending(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'assigned_to' => null,
            ];
        });
    }

    /**
     * Indicate that the file is claimed.
     */
    public function claimed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'claimed',
                'assigned_to' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the file is processing.
     */
    public function processing(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'assigned_to' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the file is completed.
     */
    public function completed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'assigned_to' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the file has a specific folder but no subfolder.
     */
    public function inFolder(Folder $folder): self
    {
        return $this->state(function (array $attributes) use ($folder) {
            return [
                'order_id' => $folder->order_id,
                'folder_id' => $folder->id,
                'subfolder_id' => null,
            ];
        });
    }

    /**
     * Indicate that the file has a specific subfolder.
     */
    public function inSubfolder(Subfolder $subfolder): self
    {
        return $this->state(function (array $attributes) use ($subfolder) {
            $folder = $subfolder->folder;

            return [
                'order_id' => $folder->order_id,
                'folder_id' => $folder->id,
                'subfolder_id' => $subfolder->id,
            ];
        });
    }
}