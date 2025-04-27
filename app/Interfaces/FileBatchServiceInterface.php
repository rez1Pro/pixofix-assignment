<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\FileClaim;
use App\Models\FileItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

interface FileBatchServiceInterface
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
    public function claimBatch(Order $order, User $user, int $batchSize = 10, ?string $directory = null): ?FileClaim;

    /**
     * Release a batch of files (unclaim them)
     * 
     * @param FileClaim $fileClaim
     * @return bool
     */
    public function releaseBatch(FileClaim $fileClaim): bool;

    /**
     * Get all files in a batch
     * 
     * @param FileClaim $fileClaim
     * @return Collection
     */
    public function getBatchFiles(FileClaim $fileClaim): Collection;

    /**
     * Mark a batch as completed
     * 
     * @param FileClaim $fileClaim
     * @return FileClaim
     */
    public function completeBatch(FileClaim $fileClaim): FileClaim;

    /**
     * Mark a single file in a batch as completed
     * 
     * @param FileClaim $fileClaim
     * @param FileItem $fileItem
     * @return FileItem
     */
    public function completeFileInBatch(FileClaim $fileClaim, FileItem $fileItem): FileItem;

    /**
     * Get all active batches for a user
     * 
     * @param User $user
     * @return Collection
     */
    public function getUserActiveBatches(User $user): Collection;

    /**
     * Get statistics for a batch
     * 
     * @param FileClaim $fileClaim
     * @return array
     */
    public function getBatchStats(FileClaim $fileClaim): array;
}