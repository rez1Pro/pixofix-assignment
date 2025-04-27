<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class OrderFileController extends Controller
{
    protected OrderServiceInterface $orderService;
    protected FileItemServiceInterface $fileItemService;

    public function __construct(
        OrderServiceInterface $orderService,
        FileItemServiceInterface $fileItemService
    ) {
        $this->orderService = $orderService;
        $this->fileItemService = $fileItemService;
    }

    /**
     * Upload new files to an existing order
     */
    public function upload(Request $request, Order $order)
    {
        try {
            $request->validate([
                'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
            ]);

            if (!$request->hasFile('files')) {
                return response()->json(['error' => 'No files uploaded'], 400);
            }

            $files = $request->file('files');
            $uploadedFiles = $this->fileItemService->uploadFiles($order, $files);

            return response()->json([
                'message' => 'Files uploaded successfully',
                'files' => $uploadedFiles
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Invalid files. Please check the file types and sizes.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('File upload error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to upload files. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a single file
     */
    public function downloadFile(FileItem $file)
    {
        // Check if file exists in storage
        if (!Storage::exists($file->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Return file as download
        return Storage::download($file->path, $file->original_name);
    }

    /**
     * Download all files from an order as a zip
     */
    public function downloadAllFiles(Order $order)
    {
        try {
            $files = $this->fileItemService->getFileItems($order);

            if ($files->isEmpty()) {
                return response()->json(['error' => 'No files available for download'], 404);
            }

            $zipName = "order_{$order->id}_files.zip";
            $zipPath = storage_path("app/temp/{$zipName}");

            // Ensure the temp directory exists with proper permissions
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    \Log::error("Failed to create temp directory at {$tempDir}");
                    return response()->json(['error' => 'Failed to create temporary directory'], 500);
                }
            }

            // Create new zip archive
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                \Log::error("Failed to create zip file at {$zipPath}");
                return response()->json(['error' => 'Could not create zip file'], 500);
            }

            $fileCount = 0;
            // Add files to the zip
            foreach ($files as $file) {
                if (Storage::exists($file->path)) {
                    $relativeName = basename($file->path);
                    $zip->addFile(storage_path("app/{$file->path}"), $relativeName);
                    $fileCount++;
                } else {
                    \Log::warning("File not found at {$file->path} when creating download zip for order {$order->id}");
                }
            }

            $zip->close();

            // Verify the zip was created successfully
            if (!file_exists($zipPath)) {
                \Log::error("Zip file not created at {$zipPath} after zip->close() for order {$order->id}");
                return response()->json(['error' => 'Failed to create download file'], 500);
            }

            if ($fileCount === 0) {
                // No files were added to the zip
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return response()->json(['error' => 'No files available for download'], 404);
            }

            // Return the zip file as a download
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error creating download zip: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e
            ]);

            return response()->json(['error' => 'Failed to create download: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download selected files from an order as a zip
     */
    public function downloadSelectedFiles(Request $request, Order $order)
    {
        try {
            $request->validate([
                'fileIds' => 'required|array',
                'fileIds.*' => 'required|integer|exists:file_items,id'
            ]);

            $fileIds = $request->input('fileIds');
            $files = FileItem::whereIn('id', $fileIds)->where('order_id', $order->id)->get();

            if ($files->isEmpty()) {
                return response()->json(['error' => 'No files available for download'], 404);
            }

            $zipName = "order_{$order->id}_selected_files.zip";
            $zipPath = storage_path("app/temp/{$zipName}");

            // Ensure the temp directory exists with proper permissions
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    \Log::error("Failed to create temp directory at {$tempDir}");
                    return response()->json(['error' => 'Failed to create temporary directory'], 500);
                }
            }

            // Create new zip archive
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                \Log::error("Failed to create zip file at {$zipPath}");
                return response()->json(['error' => 'Could not create zip file'], 500);
            }

            $fileCount = 0;
            // Add files to the zip
            foreach ($files as $file) {
                if (Storage::exists($file->path)) {
                    $relativeName = basename($file->path);
                    $zip->addFile(storage_path("app/{$file->path}"), $relativeName);
                    $fileCount++;
                } else {
                    \Log::warning("File not found at {$file->path} when creating selected files download zip for order {$order->id}");
                }
            }

            $zip->close();

            // Verify the zip was created successfully
            if (!file_exists($zipPath)) {
                \Log::error("Zip file not created at {$zipPath} after zip->close() for selected files in order {$order->id}");
                return response()->json(['error' => 'Failed to create download file'], 500);
            }

            if ($fileCount === 0) {
                // No files were added to the zip
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return response()->json(['error' => 'No files available for download'], 404);
            }

            // Return the zip file as a download
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error creating selected files download zip: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'file_ids' => $request->input('fileIds', []),
                'exception' => $e
            ]);

            return response()->json(['error' => 'Failed to create download: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download files from a specific directory in the order
     */
    public function downloadDirectoryFiles(Order $order, Request $request)
    {
        try {
            $request->validate([
                'directory' => 'required|string',
            ]);

            $directory = $request->input('directory');
            $files = $this->fileItemService->getFileItemsByDirectory($order, $directory);

            if ($files->isEmpty()) {
                return response()->json(['error' => 'No files available in this directory'], 404);
            }

            $directoryName = basename($directory);
            $zipName = "order_{$order->id}_{$directoryName}_files.zip";
            $zipPath = storage_path("app/temp/{$zipName}");

            // Ensure the temp directory exists with proper permissions
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    \Log::error("Failed to create temp directory at {$tempDir}");
                    return response()->json(['error' => 'Failed to create temporary directory'], 500);
                }
            }

            // Create new zip archive
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                \Log::error("Failed to create zip file at {$zipPath}");
                return response()->json(['error' => 'Could not create zip file'], 500);
            }

            $fileCount = 0;
            // Add files to the zip
            foreach ($files as $file) {
                if (Storage::exists($file->path)) {
                    $relativeName = basename($file->path);
                    $zip->addFile(storage_path("app/{$file->path}"), $relativeName);
                    $fileCount++;
                } else {
                    \Log::warning("File not found at {$file->path} when creating directory download zip for order {$order->id}, directory {$directory}");
                }
            }

            $zip->close();

            // Verify the zip was created successfully
            if (!file_exists($zipPath)) {
                \Log::error("Zip file not created at {$zipPath} after zip->close() for directory {$directory} in order {$order->id}");
                return response()->json(['error' => 'Failed to create download file'], 500);
            }

            if ($fileCount === 0) {
                // No files were added to the zip
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return response()->json(['error' => 'No files available for download'], 404);
            }

            // Return the zip file as a download
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error creating directory download zip: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'directory' => $request->input('directory', ''),
                'exception' => $e
            ]);

            return response()->json(['error' => 'Failed to create download: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Claim selected files
     */
    public function claimFiles(Request $request, Order $order)
    {
        $request->validate([
            'fileIds' => 'required|array',
            'fileIds.*' => 'required|integer|exists:file_items,id'
        ]);

        $fileIds = $request->input('fileIds');
        $user = $request->user();

        // Get files that belong to this order and are in pending status
        $files = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->get();

        if ($files->isEmpty()) {
            return response()->json(['error' => 'No eligible files to claim'], 404);
        }

        // Update files status to claimed and assign to current user
        foreach ($files as $file) {
            $file->status = 'claimed';
            $file->assigned_to = $user->id;
            $file->save();
        }

        // Update order status to in_progress if it's not already
        if ($order->status === 'pending') {
            $order->status = 'in_progress';
            $order->save();
        }

        return response()->json([
            'message' => 'Files claimed successfully',
            'count' => $files->count()
        ]);
    }

    /**
     * Delete a file
     */
    public function deleteFile(FileItem $file)
    {
        $result = $this->fileItemService->deleteFile($file);

        if ($result) {
            return response()->json(['message' => 'File deleted successfully']);
        }

        return response()->json(['error' => 'Failed to delete file'], 500);
    }
}