<?php

namespace App\Interfaces;

use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface FileItemServiceInterface
{
    /**
     * Get file items for an order
     */
    public function getFileItems(Order $order): Collection;

    /**
     * Get file items grouped by directory
     */
    public function getFileItemsGroupedByDirectory(Order $order): array;

    /**
     * Get file items by directory
     */
    public function getFileItemsByDirectory(Order $order, string $directory): Collection;

    /**
     * Upload files for an order
     * 
     * @param Order $order
     * @param array<UploadedFile> $files
     * @return array The array of created FileItem models
     */
    public function uploadFiles(Order $order, array $files): array;

    /**
     * Delete a file
     */
    public function deleteFile(FileItem $file): bool;
}