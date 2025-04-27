<?php

namespace App\Services;

use App\Data\FileItemData;
use App\Interfaces\FileItemServiceInterface;
use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

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
                'file_size' => $processedFile->getSize(),
                'file_type' => $processedFile->getClientOriginalExtension(),
                'mime_type' => $processedFile->getMimeType(),
                'is_processed' => true,
            ]);

            DB::commit();
            return $file;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Process edited file error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Download a file
     */
    public function downloadFile(FileItem $file): BinaryFileResponse
    {
        $path = storage_path('app/' . $file->filepath);

        if (!file_exists($path)) {
            throw new \Exception('File not found: ' . $path);
        }

        return Response::download($path, $file->original_filename ?? $file->filename);
    }

    /**
     * Upload files to a folder
     */
    public function uploadFilesToFolder(Folder $folder, array $files): array
    {
        $uploadedFiles = [];

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs(
                    'orders/' . $folder->order_id . '/' . $folder->id,
                    Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension()
                );

                $fileItem = $folder->files()->create([
                    'order_id' => $folder->order_id,
                    'name' => $originalName,
                    'original_name' => $originalName,
                    'path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                ]);

                $uploadedFiles[] = $fileItem;
            }

            DB::commit();
            return $uploadedFiles;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Upload files to folder error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upload files to a subfolder
     */
    public function uploadFilesToSubfolder(Subfolder $subfolder, array $files): array
    {
        $uploadedFiles = [];
        $folder = $subfolder->folder;

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs(
                    'orders/' . $folder->order_id . '/' . $folder->id . '/' . $subfolder->id,
                    Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension()
                );

                $fileItem = $subfolder->files()->create([
                    'order_id' => $folder->order_id,
                    'folder_id' => $folder->id,
                    'name' => $originalName,
                    'original_name' => $originalName,
                    'path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                ]);

                $uploadedFiles[] = $fileItem;
            }

            DB::commit();
            return $uploadedFiles;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Upload files to subfolder error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign a file to a user
     */
    public function assignFileToUser(FileItem $file, User $user): FileItem
    {
        return $file->assignTo($user);
    }

    /**
     * Assign multiple files to a user
     */
    public function assignMultipleFilesToUser(array $fileIds, User $user): int
    {
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($fileIds as $fileId) {
                $file = FileItem::findOrFail($fileId);
                $file->assignTo($user);
                $count++;
            }

            DB::commit();
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Assign multiple files error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update file status
     */
    public function updateFileStatus(FileItem $file, string $status): FileItem
    {
        switch ($status) {
            case 'pending':
                return $file->markAsPending();
            case 'claimed':
                return $file->markAsClaimed();
            case 'processing':
                return $file->markAsProcessing();
            case 'completed':
                return $file->markAsCompleted();
            default:
                throw new \InvalidArgumentException("Invalid status: {$status}");
        }
    }

    /**
     * Create a zip archive of multiple files
     */
    public function createZipArchiveFromFiles(Order $order, array $fileIds): string
    {
        $files = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->get();

        if ($files->isEmpty()) {
            throw new \Exception('No files found for the specified IDs');
        }

        $zipName = "order_{$order->id}_selected_files.zip";
        $zipPath = storage_path("app/temp/{$zipName}");

        // Ensure the temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Create new zip archive
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Could not create zip file');
        }

        // Add files to the zip
        foreach ($files as $file) {
            if (Storage::exists($file->filepath)) {
                // Use original filename instead of just the basename
                // Add directory prefix to maintain original structure
                $fileNameInZip = $file->original_filename ?? $file->filename;

                // If the file has a directory path, create that structure in the zip
                if ($file->directory_path) {
                    $fileNameInZip = $file->directory_path . '/' . $fileNameInZip;
                }

                $zip->addFile(storage_path("app/{$file->filepath}"), $fileNameInZip);
            }
        }

        $zip->close();

        return $zipPath;
    }
}