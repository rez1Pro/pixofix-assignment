<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\UploadedFile;

interface FileNamingServiceInterface
{
    /**
     * Generate a properly formatted filename for an uploaded file
     * 
     * @param UploadedFile $file
     * @param Order $order
     * @param string $prefix Optional prefix to add to the filename
     * @return array Returns [filename, originalFilename, directoryPath]
     */
    public function generateFilename(UploadedFile $file, Order $order, string $prefix = ''): array;

    /**
     * Generate the storage path for a file within an order
     * 
     * @param Order $order
     * @param string $directoryPath
     * @param string $filename
     * @return string
     */
    public function generateStoragePath(Order $order, string $directoryPath, string $filename): string;

    /**
     * Generate the processed storage path for a file
     * Maintains the same folder structure as the original file
     * 
     * @param FileItem $file
     * @param string $status Status to include in the filename (e.g., 'completed')
     * @return string
     */
    public function generateProcessedFilePath(FileItem $file, string $status = 'completed'): string;

    /**
     * Rename a processed file to preserve the folder structure
     * 
     * @param FileItem $file
     * @param UploadedFile $processedFile
     * @return string New filepath
     */
    public function storeProcessedFile(FileItem $file, UploadedFile $processedFile): string;

    /**
     * Get all directories in an order
     * 
     * @param Order $order
     * @return array Array of directory paths
     */
    public function getOrderDirectories(Order $order): array;

    /**
     * Get the full local path of a file
     * 
     * @param FileItem $file
     * @return string
     */
    public function getFullPath(FileItem $file): string;
}