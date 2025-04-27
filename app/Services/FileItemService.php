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
    protected FileNamingService $fileNamingService;

    public function __construct(FileNamingService $fileNamingService)
    {
        $this->fileNamingService = $fileNamingService;
    }

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
            // Use the FileNamingService to generate a proper filename
            $fileInfo = $this->fileNamingService->generateFilename($file, $order);
            $filename = $fileInfo['filename'];
            $originalFilename = $fileInfo['originalFilename'];
            $directoryPath = $fileInfo['directoryPath'];

            $fileSize = $file->getSize();
            $extension = $file->getClientOriginalExtension();

            // Generate the storage path
            $storagePath = $this->fileNamingService->generateStoragePath($order, $directoryPath, $filename);

            // Store file in the storage
            $filepath = $file->storeAs(
                dirname($storagePath),
                basename($storagePath)
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
            $directory = $file->directory_path ?? 'root';

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
        return $order->fileItems()
            ->where('directory_path', $directory)
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
            // Use the FileNamingService to generate consistent filenames
            $fileInfo = $this->fileNamingService->generateFilename($file, $order);
            $filename = $fileInfo['filename'];
            $originalFilename = $fileInfo['originalFilename'];
            $directoryPath = $fileInfo['directoryPath'];

            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();

            // Generate the storage path
            $storagePath = $this->fileNamingService->generateStoragePath($order, $directoryPath, $filename);

            // Store the file
            $path = $file->storeAs(
                dirname($storagePath),
                basename($storagePath)
            );

            // Create database record
            $fileItem = FileItem::create([
                'order_id' => $order->id,
                'filename' => $filename,
                'original_filename' => $originalFilename,
                'filepath' => $path,
                'directory_path' => $directoryPath,
                'file_type' => $extension,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
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
        if (Storage::exists($file->filepath)) {
            Storage::delete($file->filepath);
        }

        // Delete from database
        return $file->delete();
    }

    /**
     * Process (replace) a file after editing
     */
    public function processEditedFile(FileItem $file, UploadedFile $processedFile): FileItem
    {
        DB::beginTransaction();
        try {
            // Delete the existing file if it exists
            if (Storage::exists($file->filepath)) {
                Storage::delete($file->filepath);
            }

            // Store the processed file with a proper filename in the original location
            $newFilepath = $this->fileNamingService->storeProcessedFile($file, $processedFile);

            // Update the file record
            $file->update([
                'filepath' => $newFilepath,
                'is_processed' => true,
                'status' => 'completed',
                'file_size' => $processedFile->getSize(),
            ]);

            DB::commit();
            return $file;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('File processing error: ' . $e->getMessage());
            throw $e;
        }
    }
}