<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use App\Models\FileItem;
use App\Models\FileClaim;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin and editor users if they don't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role_id' => 1, // Admin role
            ]
        );

        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'password' => bcrypt('password'),
                'role_id' => 2, // Editor role
            ]
        );

        // Create 10 complete orders with structure
        for ($i = 0; $i < 10; $i++) {
            $order = Order::factory()->create([
                'created_by' => $admin->id,
            ]);

            // Create 2-5 folders per order
            $folderCount = rand(2, 5);
            for ($j = 0; $j < $folderCount; $j++) {
                $folder = Folder::factory()->create([
                    'order_id' => $order->id,
                ]);

                // 60% chance to have subfolders
                if (rand(1, 100) <= 60) {
                    $subfolderCount = rand(1, 3);
                    for ($k = 0; $k < $subfolderCount; $k++) {
                        $subfolder = Subfolder::factory()->create([
                            'folder_id' => $folder->id,
                        ]);

                        // Create 3-8 files in each subfolder
                        $fileCount = rand(3, 8);
                        for ($l = 0; $l < $fileCount; $l++) {
                            $status = $this->getRandomStatus();

                            $fileItem = FileItem::factory()->create([
                                'order_id' => $order->id,
                                'folder_id' => $folder->id,
                                'subfolder_id' => $subfolder->id,
                                'status' => $status,
                                'assigned_to' => $status === 'processing' ? $editor->id : null,
                            ]);
                        }
                    }
                }

                // Create 5-10 files directly in the folder
                $fileCount = rand(5, 10);
                for ($m = 0; $m < $fileCount; $m++) {
                    $status = $this->getRandomStatus();

                    $fileItem = FileItem::factory()->create([
                        'order_id' => $order->id,
                        'folder_id' => $folder->id,
                        'subfolder_id' => null,
                        'status' => $status,
                        'assigned_to' => $status === 'processing' ? $editor->id : null,
                    ]);
                }
            }

            // Create 0-3 file claims per order
            $claimCount = rand(0, 3);
            for ($n = 0; $n < $claimCount; $n++) {
                // Get some actual file IDs from this order
                $fileIds = FileItem::where('order_id', $order->id)
                    ->where('status', 'pending')
                    ->take(rand(1, 5))
                    ->pluck('id')
                    ->toArray();

                if (!empty($fileIds)) {
                    $isCompleted = (bool) rand(0, 1);

                    FileClaim::factory()->create([
                        'user_id' => $editor->id,
                        'order_id' => $order->id,
                        'file_ids' => $fileIds,
                        'is_completed' => $isCompleted,
                        'completed_at' => $isCompleted ? now()->subDays(rand(1, 3)) : null,
                    ]);

                    // If the claim is completed, update the files status
                    if ($isCompleted) {
                        FileItem::whereIn('id', $fileIds)->update([
                            'status' => 'completed',
                            'assigned_to' => $editor->id,
                        ]);
                    } else {
                        FileItem::whereIn('id', $fileIds)->update([
                            'status' => 'processing',
                            'assigned_to' => $editor->id,
                        ]);
                    }
                }
            }

            // Update order status based on file statuses
            $this->updateOrderStatus($order);
        }
    }

    /**
     * Get a random file status
     */
    private function getRandomStatus(): string
    {
        $statusOptions = [
            'pending' => 60, // 60% probability
            'claimed' => 10, // 10% probability
            'processing' => 20, // 20% probability
            'completed' => 10, // 10% probability 
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($statusOptions as $status => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $status;
            }
        }

        return 'pending'; // Default fallback
    }

    /**
     * Update order status based on file statuses
     */
    private function updateOrderStatus(Order $order): void
    {
        $totalFiles = FileItem::where('order_id', $order->id)->count();
        if ($totalFiles === 0) {
            $order->update(['status' => 'pending']);
            return;
        }

        $completedFiles = FileItem::where('order_id', $order->id)
            ->where('status', 'completed')
            ->count();

        $pendingFiles = FileItem::where('order_id', $order->id)
            ->where('status', 'pending')
            ->count();

        $processingFiles = FileItem::where('order_id', $order->id)
            ->where('status', 'processing')
            ->count();

        if ($completedFiles === $totalFiles) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now()->subDays(rand(1, 5)),
            ]);

            // 50% chance to approve completed orders
            if (rand(0, 1)) {
                $order->update(['approved_at' => now()->subDays(rand(1, 3))]);
            }
        } elseif ($pendingFiles === $totalFiles) {
            $order->update(['status' => 'pending']);
        } elseif ($processingFiles > 0 || $completedFiles > 0) {
            $order->update(['status' => 'in-progress']);
        }
    }
}