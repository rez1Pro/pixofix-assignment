<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\FileNamingServiceInterface;
use App\Models\Order;
use App\Models\FileItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileNamingService implements FileNamingServiceInterface
{
    /**
     * Generate a properly formatted filename for an uploaded file
     * 
     * @param UploadedFile $file
     * @param Order $order
     * @param string $prefix Optional prefix to add to the filename
     * @return array Returns [filename, originalFilename, directoryPath]
     */
    public function generateFilename(UploadedFile $file, Order $order, string $prefix = ''): array
    {
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Parse directory path and filename from original path if present
        $directoryPath = '';
        $baseFilename = $originalFilename;

        if (strpos($originalFilename, '/') !== false) {
            $parts = explode('/', $originalFilename);
            $baseFilename = array_pop($parts);
            $directoryPath = implode('/', $parts);
        }

        // Strip extension from the base filename
        $baseFilenameWithoutExt = pathinfo($baseFilename, PATHINFO_FILENAME);

        // Format: [prefix_]originalname_orderid_timestamp.extension
        $prefix = !empty($prefix) ? $prefix . '_' : '';
        $uniqueId = time() . '_' . Str::random(5);
        $newFilename = $prefix . $baseFilenameWithoutExt . '_order' . $order->id . '_' . $uniqueId . '.' . $extension;

        return [
            'filename' => $newFilename,
            'originalFilename' => $baseFilename,
            'directoryPath' => $directoryPath
        ];
    }

    /**
     * Generate the storage path for a file within an order
     * 
     * @param Order $order
     * @param string $directoryPath
     * @param string $filename
     * @return string
     */
    public function generateStoragePath(Order $order, string $directoryPath, string $filename): string
    {
        $basePath = "orders/{$order->id}";

        if (!empty($directoryPath)) {
            $basePath .= "/{$directoryPath}";
        }

        return "{$basePath}/{$filename}";
    }

    /**
     * Generate the processed storage path for a file
     * Maintains the same folder structure as the original file
     * 
     * @param FileItem $file
     * @param string $status Status to include in the filename (e.g., 'completed')
     * @return string
     */
    public function generateProcessedFilePath(FileItem $file, string $status = 'completed'): string
    {
        $pathInfo = pathinfo($file->filepath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        // Add status indicator to the filename
        $processedFilename = $filename . '_' . $status . '.' . $extension;

        return "{$directory}/{$processedFilename}";
    }

    /**
     * Rename a processed file to preserve the folder structure
     * 
     * @param FileItem $file
     * @param UploadedFile $processedFile
     * @return string New filepath
     */
    public function storeProcessedFile(FileItem $file, UploadedFile $processedFile): string
    {
        $newFilepath = $this->generateProcessedFilePath($file);

        // Store the file in the same directory structure as the original
        $storageDir = dirname($newFilepath);

        // Extract the filename only (no path)
        $newFilename = basename($newFilepath);

        // Store the file
        return $processedFile->storeAs($storageDir, $newFilename);
    }

    /**
     * Get all directories in an order
     * 
     * @param Order $order
     * @return array Array of directory paths
     */
    public function getOrderDirectories(Order $order): array
    {
        $files = $order->fileItems()->get();
        $directories = [];

        foreach ($files as $file) {
            if (!empty($file->directory_path) && !in_array($file->directory_path, $directories)) {
                $directories[] = $file->directory_path;
            }
        }

        sort($directories);
        return $directories;
    }

    /**
     * Get the full local path of a file
     * 
     * @param FileItem $file
     * @return string
     */
    public function getFullPath(FileItem $file): string
    {
        return storage_path('app/' . $file->filepath);
    }
}