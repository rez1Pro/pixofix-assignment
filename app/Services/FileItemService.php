<?php

namespace App\Services;

use App\Data\FileItemData;
use App\Interfaces\FileItemServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * Get file items for an order
     */
    public function getFileItems(Order $order): Collection
    {
        return $order->fileItems;
    }

    /**
     * Get file items grouped by directory
     */
    public function getFileItemsGroupedByDirectory(Order $order): array
    {
        $fileItems = $this->getFileItems($order);
        $groupedFiles = [];

        foreach ($fileItems as $file) {
            $directory = dirname($file->path);
            $directory = str_replace('orders/' . $order->id . '/', '', $directory);

            if (!isset($groupedFiles[$directory])) {
                $groupedFiles[$directory] = [];
            }

            $groupedFiles[$directory][] = $file;
        }

        // Sort directories alphabetically
        ksort($groupedFiles);

        return $groupedFiles;
    }

    /**
     * Get file items by directory
     */
    public function getFileItemsByDirectory(Order $order, string $directory): Collection
    {
        $searchDirectory = 'orders/' . $order->id . '/' . ltrim($directory, '/');

        return $order->fileItems()
            ->where('path', 'like', $searchDirectory . '/%')
            ->get();
    }

    /**
     * Upload files for an order
     */
    public function uploadFiles(Order $order, array $files): array
    {
        $uploadedFiles = [];

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                // Validate file
                if (!$file->isValid()) {
                    throw new \Exception("File upload failed: {$file->getClientOriginalName()}");
                }

                $uploadedFile = $this->uploadFile($order, $file);
                $uploadedFiles[] = $uploadedFile;
            }
            DB::commit();
            return $uploadedFiles;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('File upload service error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload a single file
     */
    private function uploadFile(Order $order, UploadedFile $file): FileItem
    {
        try {
            // Generate a unique filename
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filename = pathinfo($originalFilename, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;

            // Determine directory path
            $directoryPath = '';
            if (strpos($originalFilename, '/') !== false) {
                $parts = explode('/', $originalFilename);
                $filename = array_pop($parts);
                $directoryPath = implode('/', $parts);
            }

            // Store file in storage
            $path = $file->storeAs(
                "orders/{$order->id}" . ($directoryPath ? "/{$directoryPath}" : ""),
                $filename
            );

            // Create database record
            $fileItem = FileItem::create([
                'order_id' => $order->id,
                'name' => $filename,
                'original_name' => $originalFilename,
                'path' => $path,
                'directory' => $directoryPath,
                'type' => $extension,
                'size' => $fileSize,
                'mime_type' => $file->getMimeType(),
                'status' => 'pending',
            ]);

            return $fileItem;
        } catch (\Exception $e) {
            \Log::error('Individual file upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a file
     */
    public function deleteFile(FileItem $file): bool
    {
        // Delete from storage
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        // Delete from database
        return $file->delete();
    }
}