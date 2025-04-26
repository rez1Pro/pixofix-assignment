<?php

namespace App\Interfaces;

use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\DataCollection;

interface FileItemServiceInterface
{
    /**
     * Get all file items for an order
     */
    public function getFileItemsByOrder(Order $order): DataCollection;

    /**
     * Get file item by ID
     */
    public function getFileItemById(int $id): FileItem;

    /**
     * Process and store uploaded files for an order
     */
    public function processFiles(Order $order, array $files): void;

    /**
     * Mark a file as claimed
     */
    public function markAsClaimed(FileItem $fileItem): FileItem;

    /**
     * Mark a file as processing
     */
    public function markAsProcessing(FileItem $fileItem): FileItem;

    /**
     * Mark a file as completed
     */
    public function markAsCompleted(FileItem $fileItem): FileItem;

    /**
     * Get file items grouped by directory
     */
    public function getFileItemsGroupedByDirectory(Order $order): array;
}