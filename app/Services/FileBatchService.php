<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\FileBatchServiceInterface;
use App\Models\FileClaim;
use App\Models\FileItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FileBatchService implements FileBatchServiceInterface
{
    /**
     * Claim a batch of files for processing
     * 
     * @param Order $order
     * @param User $user
     * @param int $batchSize Number of files to claim
     * @param string|null $directory Optional directory to limit the claim to
     * @return FileClaim|null
     */
    public function claimBatch(Order $order, User $user, int $batchSize = 10, ?string $directory = null): ?FileClaim
    {
        // Start a transaction
        return DB::transaction(function () use ($order, $user, $batchSize, $directory) {
            // Find available files (not yet claimed or completed)
            $query = FileItem::where('order_id', $order->id)
                ->where('status', 'pending')
                ->whereNull('assigned_to');

            // If directory is specified, filter by directory
            if ($directory) {
                $query->where('directory_path', $directory);
            }

            // Lock the selected files for update to prevent race conditions
            $filesToClaim = $query->limit($batchSize)
                ->lockForUpdate()
                ->get();

            if ($filesToClaim->isEmpty()) {
                return null;
            }

            // Create the file claim
            $fileIds = $filesToClaim->pluck('id')->toArray();

            $fileClaim = FileClaim::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'file_ids' => $fileIds,
                'claimed_at' => now(),
                'is_completed' => false,
            ]);

            // Update the file items to show they're claimed
            FileItem::whereIn('id', $fileIds)->update([
                'status' => 'claimed',
                'assigned_to' => $user->id,
            ]);

            return $fileClaim;
        });
    }

    /**
     * Release a batch of files (unclaim them)
     * 
     * @param FileClaim $fileClaim
     * @return bool
     */
    public function releaseBatch(FileClaim $fileClaim): bool
    {
        return DB::transaction(function () use ($fileClaim) {
            // Update the files to be unclaimed
            FileItem::whereIn('id', $fileClaim->file_ids)
                ->where('status', 'claimed')
                ->update([
                    'status' => 'pending',
                    'assigned_to' => null,
                ]);

            // Delete the claim
            return $fileClaim->delete();
        });
    }

    /**
     * Get all files in a batch
     * 
     * @param FileClaim $fileClaim
     * @return Collection
     */
    public function getBatchFiles(FileClaim $fileClaim): Collection
    {
        return FileItem::whereIn('id', $fileClaim->file_ids)->get();
    }

    /**
     * Mark a batch as completed
     * 
     * @param FileClaim $fileClaim
     * @return FileClaim
     */
    public function completeBatch(FileClaim $fileClaim): FileClaim
    {
        return DB::transaction(function () use ($fileClaim) {
            // Mark the claim as completed
            $fileClaim->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // Mark all files in the batch as completed
            FileItem::whereIn('id', $fileClaim->file_ids)
                ->update([
                    'status' => 'completed',
                    'is_processed' => true,
                ]);

            // Check if the order is now complete
            $order = $fileClaim->order;
            $pendingFiles = $order->fileItems()->where('status', '!=', 'completed')->count();

            if ($pendingFiles === 0) {
                $order->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            return $fileClaim;
        });
    }

    /**
     * Mark a single file in a batch as completed
     * 
     * @param FileClaim $fileClaim
     * @param FileItem $fileItem
     * @return FileItem
     */
    public function completeFileInBatch(FileClaim $fileClaim, FileItem $fileItem): FileItem
    {
        // Ensure the file is part of the batch
        if (!in_array($fileItem->id, $fileClaim->file_ids)) {
            throw new \InvalidArgumentException('File is not part of the specified batch');
        }

        // Mark the file as completed
        $fileItem->update([
            'status' => 'completed',
            'is_processed' => true,
        ]);

        // Check if all files in the batch are now completed
        $uncompletedCount = FileItem::whereIn('id', $fileClaim->file_ids)
            ->where('status', '!=', 'completed')
            ->count();

        if ($uncompletedCount === 0) {
            $this->completeBatch($fileClaim);
        }

        return $fileItem;
    }

    /**
     * Get all active batches for a user
     * 
     * @param User $user
     * @return Collection
     */
    public function getUserActiveBatches(User $user): Collection
    {
        return FileClaim::where('user_id', $user->id)
            ->where('is_completed', false)
            ->with('order')
            ->get();
    }

    /**
     * Get statistics for a batch
     * 
     * @param FileClaim $fileClaim
     * @return array
     */
    public function getBatchStats(FileClaim $fileClaim): array
    {
        $totalFiles = count($fileClaim->file_ids);
        $completedFiles = FileItem::whereIn('id', $fileClaim->file_ids)
            ->where('status', 'completed')
            ->count();

        $pendingFiles = $totalFiles - $completedFiles;
        $progressPercentage = ($totalFiles > 0) ? round(($completedFiles / $totalFiles) * 100) : 0;

        return [
            'totalFiles' => $totalFiles,
            'completedFiles' => $completedFiles,
            'pendingFiles' => $pendingFiles,
            'progressPercentage' => $progressPercentage,
        ];
    }
}