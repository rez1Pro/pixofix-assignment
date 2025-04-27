<?php

namespace App\Interfaces;

use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * Process (replace) a file after editing
     */
    public function processEditedFile(FileItem $file, UploadedFile $processedFile): FileItem;

    /**
     * Download a file
     */
    public function downloadFile(FileItem $file): BinaryFileResponse;

    /**
     * Upload files to a folder
     */
    public function uploadFilesToFolder(Folder $folder, array $files): array;

    /**
     * Upload files to a subfolder
     */
    public function uploadFilesToSubfolder(Subfolder $subfolder, array $files): array;

    /**
     * Assign a file to a user
     */
    public function assignFileToUser(FileItem $file, User $user): FileItem;

    /**
     * Assign multiple files to a user
     */
    public function assignMultipleFilesToUser(array $fileIds, User $user): int;

    /**
     * Update file status
     */
    public function updateFileStatus(FileItem $file, string $status): FileItem;

    /**
     * Create a zip archive of multiple files
     */
    public function createZipArchiveFromFiles(Order $order, array $fileIds): string;
}