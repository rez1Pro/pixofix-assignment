<?php

namespace App\Services;

use App\Data\FileItemData;
use App\Interfaces\FileItemServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\DataCollection;

class FileItemService implements FileItemServiceInterface
{
    /**
     * Get all file items for an order
     */
    public function getFileItemsByOrder(Order $order): DataCollection
    {
        $fileItems = $order->fileItems()->get();

        return FileItemData::collect($fileItems);
    }

    /**
     * Get file item by ID
     */
    public function getFileItemById(int $id): FileItem
    {
        return FileItem::findOrFail($id);
    }

    /**
     * Process and store uploaded files for an order
     */
    public function processFiles(Order $order, array $files): void
    {
        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filename = pathinfo($originalFilename, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;

            // Parse directory path from filename if it's in a structure like "folder1/folder2/filename.ext"
            $directoryPath = null;
            if (strpos($originalFilename, '/') !== false) {
                $parts = explode('/', $originalFilename);
                $filename = array_pop($parts);
                $directoryPath = implode('/', $parts);
            }

            // Store file in the storage
            $filepath = $file->storeAs(
                "orders/{$order->id}" . ($directoryPath ? "/{$directoryPath}" : ""),
                $filename
            );

            // Create file record
            FileItem::create([
                'order_id' => $order->id,
                'filename' => $filename,
                'original_filename' => $originalFilename,
                'filepath' => $filepath,
                'directory_path' => $directoryPath,
                'file_type' => $extension,
                'file_size' => $fileSize,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Mark a file as claimed
     */
    public function markAsClaimed(FileItem $fileItem): FileItem
    {
        return $fileItem->markAsClaimed();
    }

    /**
     * Mark a file as processing
     */
    public function markAsProcessing(FileItem $fileItem): FileItem
    {
        return $fileItem->markAsProcessing();
    }

    /**
     * Mark a file as completed
     */
    public function markAsCompleted(FileItem $fileItem): FileItem
    {
        return $fileItem->markAsCompleted();
    }

    /**
     * Get file items grouped by directory
     */
    public function getFileItemsGroupedByDirectory(Order $order): array
    {
        $fileItems = $order->fileItems;

        // Group files by directory path
        $grouped = $fileItems->groupBy('directory_path')->toArray();

        // Convert to a more structured format
        $result = [];
        foreach ($grouped as $directory => $files) {
            $result[$directory ?? 'root'] = FileItemData::collect($files);
        }

        return $result;
    }
}